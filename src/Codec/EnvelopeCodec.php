<?php

declare(strict_types=1);

namespace App\Enveloping\Codec;

use App\Enveloping\DTO\EnvelopeTransportDTO;
use App\Enveloping\Registry\EnvelopeAttributeCodecRegistry;
use App\Enveloping\ValueObject\Envelope;

/**
 * Converts envelopes to/from a transport DTO while delegating subject encoding.
 */
final readonly class EnvelopeCodec
{
    public function __construct(private EnvelopeAttributeCodecRegistry $attributeCodecs)
    {
    }

    /**
     * @param callable(mixed): mixed $encodeSubject
     */
    public function encode(Envelope $envelope, callable $encodeSubject): EnvelopeTransportDTO
    {
        $attributes = [];
        foreach ($envelope->attributes() as $attribute) {
            $attributes[] = $this->attributeCodecs->encode($attribute);
        }

        return new EnvelopeTransportDTO(
            $encodeSubject($envelope->subject),
            $attributes,
        );
    }

    /**
     * Reconstructs an Envelope while delegating subject decoding to the composing layer.
     *
     * @param callable(mixed): mixed $decodeSubject
     */
    public function decode(EnvelopeTransportDTO $transport, callable $decodeSubject): Envelope
    {
        $attributes = [];
        foreach ($transport->attributes as $attribute) {
            $attributes[] = $this->attributeCodecs->decode($attribute);
        }

        return new Envelope(
            $decodeSubject($transport->subject),
            $attributes,
        );
    }
}
