<?php

declare(strict_types=1);

namespace App\Enveloping\Registry;

use App\Enveloping\Codec\EnvelopeAttributeCodec;
use App\Enveloping\DTO\EnvelopeAttributeTransportDTO;
use App\Enveloping\Exception\EnvelopeCodecException;
use App\Enveloping\Validator\EnvelopeTransportTypeValidator;
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

    /** @param iterable<mixed> $codecs */
    public function __construct(iterable $codecs)
    {
        $normalized = [];
        $byType = [];
        $typesByCodec = [];

        foreach ($codecs as $codec) {
            if (!$codec instanceof EnvelopeAttributeCodec) {
                throw new EnvelopeCodecException('Envelope attribute codec registry items must implement '.EnvelopeAttributeCodec::class.'.');
            }

            $normalized[] = $codec;
            $codecTypes = [];
            $transportTypes = $codec->transportTypes();

            // Runtime implementations are not constrained by interface PHPDoc.
            // @phpstan-ignore function.alreadyNarrowedType
            if (!array_is_list($transportTypes)) {
                throw new EnvelopeCodecException('Envelope attribute codec transport types must be a list.');
            }

            foreach ($transportTypes as $type) {
                // Runtime implementations are not constrained by interface PHPDoc.
                // @phpstan-ignore function.alreadyNarrowedType
                if (!\is_string($type) || !EnvelopeTransportTypeValidator::isValid($type)) {
                    throw new EnvelopeCodecException('Envelope attribute codec transport type must match /^[a-z][a-z0-9._-]*$/.');
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

        $attribute = $codec->decode($transport);
        if (!$codec->supportsAttribute($attribute)) {
            throw new EnvelopeCodecException(\sprintf('Envelope attribute codec %s decoded transport type %s into an unsupported runtime attribute %s.', $codec::class, $transport->type, $attribute::class));
        }

        foreach ($this->codecs as $candidate) {
            if ($candidate === $codec || !$candidate->supportsAttribute($attribute)) {
                continue;
            }

            throw new EnvelopeCodecException(\sprintf('Decoded Envelope attribute %s is supported by multiple codecs.', $attribute::class));
        }

        return $attribute;
    }
}
