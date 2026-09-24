<?php

declare(strict_types=1);

namespace App\Enveloping\Factory;

use App\Enveloping\ValueObject\Envelope;
use App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface;

/**
 * Creates immutable envelopes without requiring subjects to implement package contracts.
 */
final class EnvelopeFactory
{
    /**
     * Wraps a subject with only the contextual attributes supplied explicitly
     * by the composing layer.
     */
    public function create(mixed $subject, EnvelopeAttributeInterface ...$attributes): Envelope
    {
        return new Envelope($subject, $attributes);
    }

    /**
     * Creates a child envelope that explicitly inherits all parent context.
     */
    public function inherit(Envelope $parent, mixed $subject): Envelope
    {
        return new Envelope($subject, $parent->attributes());
    }

    /**
     * Creates a child envelope that inherits only selected parent context types.
     *
     * Selection is polymorphic and preserves the parent's original attribute order.
     *
     * @param class-string<EnvelopeAttributeInterface> ...$attributeClasses
     */
    public function inheritOnly(Envelope $parent, mixed $subject, string ...$attributeClasses): Envelope
    {
        if ([] === $attributeClasses) {
            return new Envelope($subject);
        }

        foreach ($attributeClasses as $attributeClass) {
            // Reuse Envelope's runtime class-string validation contract.
            $parent->all($attributeClass);
        }

        $attributes = [];
        foreach ($parent->attributes() as $attribute) {
            foreach ($attributeClasses as $attributeClass) {
                if ($attribute instanceof $attributeClass) {
                    $attributes[] = $attribute;

                    break;
                }
            }
        }

        return new Envelope($subject, $attributes);
    }
}
