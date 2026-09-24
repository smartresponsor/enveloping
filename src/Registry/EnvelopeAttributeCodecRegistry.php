<?php

declare(strict_types=1);

namespace App\Enveloping\Registry;

use App\Enveloping\Codec\EnvelopeAttributeCodec;
use App\Enveloping\DTO\EnvelopeAttributeTransportDTO;
use App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface;

/**
 * Selects an attribute codec without teaching Enveloping about consumer domains.
 */
final readonly class EnvelopeAttributeCodecRegistry
{
    /** @var list<EnvelopeAttributeCodec> */
    private array $codecs;

    /** @param iterable<EnvelopeAttributeCodec> $codecs */
    public function __construct(iterable $codecs)
    {
        $normalized = [];
        foreach ($codecs as $codec) {
            $normalized[] = $codec;
        }

        $this->codecs = $normalized;
    }

    /**
     * Delegates encoding to the first registered codec that supports the attribute.
     */
    public function encode(EnvelopeAttributeInterface $attribute): EnvelopeAttributeTransportDTO
    {
        foreach ($this->codecs as $codec) {
            if ($codec->supports($attribute)) {
                return $codec->encode($attribute);
            }
        }

        throw new \InvalidArgumentException('No envelope attribute codec supports '.$attribute::class.'.');
    }

    /**
     * Delegates decoding to the first registered codec that supports the transport type.
     */
    public function decode(EnvelopeAttributeTransportDTO $transport): EnvelopeAttributeInterface
    {
        foreach ($this->codecs as $codec) {
            if ($codec->supports($transport->type)) {
                return $codec->decode($transport);
            }
        }

        throw new \InvalidArgumentException('No envelope attribute codec supports transport type '.$transport->type.'.');
    }
}
