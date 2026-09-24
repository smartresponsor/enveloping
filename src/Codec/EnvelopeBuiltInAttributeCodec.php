<?php

declare(strict_types=1);

namespace App\Enveloping\Codec;

use App\Enveloping\DTO\EnvelopeAttributeTransportDTO;
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
    /**
     * Recognizes only the generic attribute vocabulary owned by Enveloping.
     */
    public function supports(EnvelopeAttributeInterface|string $attribute): bool
    {
        $class = \is_string($attribute) ? $attribute : $attribute::class;

        return \in_array($class, [
            EnvelopeActorAttribute::class,
            EnvelopeCausationAttribute::class,
            EnvelopeCorrelationAttribute::class,
            EnvelopeOriginAttribute::class,
        ], true);
    }

    /**
     * Encodes a supported built-in attribute to a stable scalar payload.
     */
    public function encode(EnvelopeAttributeInterface $attribute): EnvelopeAttributeTransportDTO
    {
        return match (true) {
            $attribute instanceof EnvelopeActorAttribute => new EnvelopeAttributeTransportDTO($attribute::class, ['identity' => $attribute->identity]),
            $attribute instanceof EnvelopeCausationAttribute => new EnvelopeAttributeTransportDTO($attribute::class, ['id' => $attribute->id]),
            $attribute instanceof EnvelopeCorrelationAttribute => new EnvelopeAttributeTransportDTO($attribute::class, ['id' => $attribute->id]),
            $attribute instanceof EnvelopeOriginAttribute => new EnvelopeAttributeTransportDTO($attribute::class, ['source' => $attribute->source]),
            default => throw new \InvalidArgumentException('Unsupported envelope attribute '.$attribute::class.'.'),
        };
    }

    /**
     * Reconstructs a supported built-in attribute from validated transport data.
     */
    public function decode(EnvelopeAttributeTransportDTO $transport): EnvelopeAttributeInterface
    {
        return match ($transport->type) {
            EnvelopeActorAttribute::class => new EnvelopeActorAttribute($this->stringPayload($transport, 'identity')),
            EnvelopeCausationAttribute::class => new EnvelopeCausationAttribute($this->stringPayload($transport, 'id')),
            EnvelopeCorrelationAttribute::class => new EnvelopeCorrelationAttribute($this->stringPayload($transport, 'id')),
            EnvelopeOriginAttribute::class => new EnvelopeOriginAttribute($this->stringPayload($transport, 'source')),
            default => throw new \InvalidArgumentException('Unsupported envelope attribute transport type '.$transport->type.'.'),
        };
    }

    private function stringPayload(EnvelopeAttributeTransportDTO $transport, string $key): string
    {
        $value = $transport->payload[$key] ?? null;
        if (!\is_string($value)) {
            throw new \InvalidArgumentException(\sprintf('Envelope attribute %s requires string payload key %s.', $transport->type, $key));
        }

        return $value;
    }
}
