<?php

declare(strict_types=1);

namespace App\Enveloping\Registry;

use App\Enveloping\Codec\EnvelopeAttributeCodec;
use App\Enveloping\DTO\EnvelopeAttributeTransportDTO;
use App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface;

/**
 * Selects attribute codecs without teaching Enveloping about consumer domains.
 *
 * Transport type ownership is indexed once and must be globally unambiguous.
 */
final readonly class EnvelopeAttributeCodecRegistry
{
    /** @var list<EnvelopeAttributeCodec> */
    private array $codecs;

    /** @var array<non-empty-string, EnvelopeAttributeCodec> */
    private array $codecsByType;

    /** @param iterable<EnvelopeAttributeCodec> $codecs */
    public function __construct(iterable $codecs)
    {
        $normalized = [];
        $byType = [];

        foreach ($codecs as $codec) {
            $normalized[] = $codec;

            foreach ($codec->transportTypes() as $type) {
                if ('' === $type) {
                    throw new \InvalidArgumentException('Envelope attribute codec transport type must be non-empty.');
                }

                if (isset($byType[$type])) {
                    throw new \InvalidArgumentException(\sprintf('Envelope attribute transport type %s is owned by multiple codecs.', $type));
                }

                $byType[$type] = $codec;
            }
        }

        $this->codecs = $normalized;
        $this->codecsByType = $byType;
    }

    /**
     * Delegates encoding to the first registered codec that supports the runtime attribute.
     */
    public function encode(EnvelopeAttributeInterface $attribute): EnvelopeAttributeTransportDTO
    {
        foreach ($this->codecs as $codec) {
            if ($codec->supportsAttribute($attribute)) {
                return $codec->encode($attribute);
            }
        }

        throw new \InvalidArgumentException('No envelope attribute codec supports '.$attribute::class.'.');
    }

    /**
     * Delegates decoding to the codec that uniquely owns the stable transport type.
     */
    public function decode(EnvelopeAttributeTransportDTO $transport): EnvelopeAttributeInterface
    {
        $codec = $this->codecsByType[$transport->type] ?? null;
        if (null === $codec) {
            throw new \InvalidArgumentException('No envelope attribute codec supports transport type '.$transport->type.'.');
        }

        return $codec->decode($transport);
    }
}
