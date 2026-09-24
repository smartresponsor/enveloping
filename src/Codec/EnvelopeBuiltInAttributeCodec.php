<?php

declare(strict_types=1);

namespace App\Enveloping\Codec;

use App\Enveloping\DTO\EnvelopeAttributeTransportDTO;
use App\Enveloping\Exception\EnvelopeTransportException;
use App\Enveloping\ValueObject\EnvelopeActorAttribute;
use App\Enveloping\ValueObject\EnvelopeCausationAttribute;
use App\Enveloping\ValueObject\EnvelopeCorrelationAttribute;
use App\Enveloping\ValueObject\EnvelopeOriginAttribute;
use App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface;

/**
 * Handles the generic attribute vocabulary owned by Enveloping itself.
 */
final class EnvelopeBuiltInAttributeCodec implements EnvelopeAttributeCodec
{
    private const string TYPE_ACTOR = 'actor';
    private const string TYPE_CAUSATION = 'causation';
    private const string TYPE_CORRELATION = 'correlation';
    private const string TYPE_ORIGIN = 'origin';

    /**
     * Recognizes only the generic runtime attribute vocabulary owned by Enveloping.
     */
    public function supportsAttribute(EnvelopeAttributeInterface $attribute): bool
    {
        return $attribute instanceof EnvelopeActorAttribute
            || $attribute instanceof EnvelopeCausationAttribute
            || $attribute instanceof EnvelopeCorrelationAttribute
            || $attribute instanceof EnvelopeOriginAttribute;
    }

    /**
     * Declares the stable wire identifiers owned by the built-in vocabulary.
     *
     * @return list<string>
     */
    public function transportTypes(): array
    {
        return [
            self::TYPE_ACTOR,
            self::TYPE_CAUSATION,
            self::TYPE_CORRELATION,
            self::TYPE_ORIGIN,
        ];
    }

    /**
     * Encodes a supported built-in attribute to a stable scalar payload.
     */
    public function encode(EnvelopeAttributeInterface $attribute): EnvelopeAttributeTransportDTO
    {
        return match (true) {
            $attribute instanceof EnvelopeActorAttribute => new EnvelopeAttributeTransportDTO(self::TYPE_ACTOR, ['identity' => $attribute->identity]),
            $attribute instanceof EnvelopeCausationAttribute => new EnvelopeAttributeTransportDTO(self::TYPE_CAUSATION, ['id' => $attribute->id]),
            $attribute instanceof EnvelopeCorrelationAttribute => new EnvelopeAttributeTransportDTO(self::TYPE_CORRELATION, ['id' => $attribute->id]),
            $attribute instanceof EnvelopeOriginAttribute => new EnvelopeAttributeTransportDTO(self::TYPE_ORIGIN, ['source' => $attribute->source]),
            default => throw new \InvalidArgumentException('Unsupported envelope attribute '.$attribute::class.'.'),
        };
    }

    /**
     * Reconstructs a supported built-in attribute from validated transport data.
     */
    public function decode(EnvelopeAttributeTransportDTO $transport): EnvelopeAttributeInterface
    {
        return match ($transport->type) {
            self::TYPE_ACTOR => new EnvelopeActorAttribute($this->stringPayload($transport, 'identity')),
            self::TYPE_CAUSATION => new EnvelopeCausationAttribute($this->stringPayload($transport, 'id')),
            self::TYPE_CORRELATION => new EnvelopeCorrelationAttribute($this->stringPayload($transport, 'id')),
            self::TYPE_ORIGIN => new EnvelopeOriginAttribute($this->stringPayload($transport, 'source')),
            default => throw new EnvelopeTransportException('Unsupported envelope attribute transport type '.$transport->type.'.'),
        };
    }

    private function stringPayload(EnvelopeAttributeTransportDTO $transport, string $key): string
    {
        $keys = array_keys($transport->payload);
        if ([$key] !== $keys) {
            throw new EnvelopeTransportException(\sprintf('Envelope attribute %s payload must contain exactly key %s.', $transport->type, $key));
        }

        $value = $transport->payload[$key] ?? null;
        if (!\is_string($value)) {
            throw new EnvelopeTransportException(\sprintf('Envelope attribute %s requires string payload key %s.', $transport->type, $key));
        }

        return $value;
    }
}
