<?php

declare(strict_types=1);

namespace App\Enveloping\Registry;

use App\Enveloping\Codec\EnvelopeAttributeCodec;
use App\Enveloping\DTO\EnvelopeAttributeTransportDTO;
use App\Enveloping\Exception\EnvelopeCodecException;
use App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface;

/**
 * Selects attribute codecs without teaching Enveloping about consumer domains.
 *
 * Transport type ownership is indexed once and must be globally unambiguous.
 * Runtime attribute encoding must also resolve to exactly one codec.
 */
final readonly class EnvelopeAttributeCodecRegistry
{
    /** @var list<EnvelopeAttributeCodec> */
    private array $codecs;

    /** @var array<non-empty-string, EnvelopeAttributeCodec> */
    private array $codecsByType;

    /**
     * @var array<int, list<non-empty-string>>
     */
    private array $typesByCodec;

    /** @param iterable<EnvelopeAttributeCodec> $codecs */
    public function __construct(iterable $codecs)
    {
        $normalized = [];
        $byType = [];
        $typesByCodec = [];

        foreach ($codecs as $codec) {
            $normalized[] = $codec;
            $codecTypes = [];

            foreach ($codec->transportTypes() as $type) {
                if ('' === $type) {
                    throw new EnvelopeCodecException('Envelope attribute codec transport type must be non-empty.');
                }

                if (isset($byType[$type])) {
                    throw new EnvelopeCodecException(\sprintf('Envelope attribute transport type %s is owned by multiple codecs.', $type));
                }

                $byType[$type] = $codec;
                $codecTypes[] = $type;
            }

            $typesByCodec[spl_object_id($codec)] = $codecTypes;
        }

        $this->codecs = $normalized;
        $this->codecsByType = $byType;
        $this->typesByCodec = $typesByCodec;
    }

    /**
     * Encodes a runtime attribute only when exactly one registered codec owns it.
     */
    public function encode(EnvelopeAttributeInterface $attribute): EnvelopeAttributeTransportDTO
    {
        $owner = null;

        foreach ($this->codecs as $codec) {
            if (!$codec->supportsAttribute($attribute)) {
                continue;
            }

            if (null !== $owner) {
                throw new EnvelopeCodecException(\sprintf('Envelope attribute %s is supported by multiple codecs.', $attribute::class));
            }

            $owner = $codec;
        }

        if (null === $owner) {
            throw new EnvelopeCodecException('No envelope attribute codec supports '.$attribute::class.'.');
        }

        $transport = $owner->encode($attribute);
        if (!\in_array($transport->type, $this->typesByCodec[spl_object_id($owner)] ?? [], true)) {
            throw new EnvelopeCodecException(\sprintf('Envelope attribute codec %s emitted undeclared transport type %s.', $owner::class, $transport->type));
        }

        return $transport;
    }

    /**
     * Delegates decoding to the codec that uniquely owns the stable transport type.
     */
    public function decode(EnvelopeAttributeTransportDTO $transport): EnvelopeAttributeInterface
    {
        $codec = $this->codecsByType[$transport->type] ?? null;
        if (null === $codec) {
            throw new EnvelopeCodecException('No envelope attribute codec supports transport type '.$transport->type.'.');
        }

        return $codec->decode($transport);
    }
}
