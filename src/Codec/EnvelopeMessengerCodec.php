<?php

declare(strict_types=1);

namespace App\Enveloping\Codec;

use App\Enveloping\DTO\EnvelopeTransportDTO;
use App\Enveloping\Message\EnvelopeContextStamp;
use App\Enveloping\Registry\EnvelopeAttributeCodecRegistry;
use App\Enveloping\ValueObject\Envelope as ContextEnvelope;
use Symfony\Component\Messenger\Envelope as MessengerEnvelope;

/**
 * Bridges Enveloping context to Symfony Messenger stamps without owning
 * business-message serialization.
 */
final readonly class EnvelopeMessengerCodec
{
    public function __construct(private EnvelopeAttributeCodecRegistry $attributeCodecs)
    {
    }

    /**
     * Wraps an object subject in a Symfony Messenger Envelope carrying one
     * transportable Enveloping context stamp.
     */
    public function toMessenger(ContextEnvelope $envelope): MessengerEnvelope
    {
        if (!\is_object($envelope->subject)) {
            throw new \InvalidArgumentException('Symfony Messenger messages must be objects.');
        }

        $attributes = [];
        foreach ($envelope->attributes() as $attribute) {
            $attributes[] = $this->attributeCodecs->encode($attribute);
        }

        return new MessengerEnvelope($envelope->subject, [
            EnvelopeContextStamp::fromTransportAttributes(
                $attributes,
                EnvelopeTransportDTO::CURRENT_VERSION,
            ),
        ]);
    }

    /**
     * Reconstructs Enveloping context from the most recent context stamp.
     *
     * A Messenger Envelope without an Enveloping stamp is still valid and
     * becomes an Envelope with empty contextual metadata.
     */
    public function fromMessenger(MessengerEnvelope $envelope): ContextEnvelope
    {
        $stamp = $envelope->last(EnvelopeContextStamp::class);
        if (null === $stamp) {
            return new ContextEnvelope($envelope->getMessage());
        }

        if (EnvelopeTransportDTO::CURRENT_VERSION !== $stamp->version) {
            throw new \InvalidArgumentException(\sprintf('Unsupported envelope context stamp version %d; expected %d.', $stamp->version, EnvelopeTransportDTO::CURRENT_VERSION));
        }

        $attributes = [];
        foreach ($stamp->transportAttributes() as $attribute) {
            $attributes[] = $this->attributeCodecs->decode($attribute);
        }

        return new ContextEnvelope(
            $envelope->getMessage(),
            $attributes,
        );
    }
}
