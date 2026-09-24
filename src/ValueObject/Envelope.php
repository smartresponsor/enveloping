<?php

declare(strict_types=1);

namespace App\Enveloping\ValueObject;

use App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface;

/**
 * Wraps an arbitrary subject with immutable typed execution-context values.
 *
 * The subject remains unaware of Enveloping and its intrinsic state is never
 * mutated when contextual attributes are added, replaced, removed, or rebound.
 */
final readonly class Envelope
{
    /** @var list<EnvelopeAttributeInterface> */
    private array $attributes;

    /** @param iterable<EnvelopeAttributeInterface> $attributes */
    public function __construct(public mixed $subject, iterable $attributes = [])
    {
        $normalized = [];
        foreach ($attributes as $attribute) {
            $normalized[] = $attribute;
        }

        $this->attributes = $normalized;
    }

    /**
     * Returns a new envelope with additional typed contextual attributes.
     */
    public function with(EnvelopeAttributeInterface ...$attributes): self
    {
        if ([] === $attributes) {
            return $this;
        }

        return new self($this->subject, [...$this->attributes, ...$attributes]);
    }

    /**
     * Returns a new envelope whose subject is replaced while context is preserved.
     */
    public function withSubject(mixed $subject): self
    {
        if ($subject === $this->subject) {
            return $this;
        }

        return new self($subject, $this->attributes);
    }

    /**
     * Replaces every attribute compatible with the new attribute type.
     *
     * Replacement is explicit so Enveloping does not impose singleton or
     * multi-value cardinality rules on consumers.
     */
    public function replace(EnvelopeAttributeInterface $attribute): self
    {
        return $this->without($attribute::class)->with($attribute);
    }

    /**
     * Reports whether at least one compatible attribute is attached.
     *
     * @param class-string<EnvelopeAttributeInterface> $attributeClass
     */
    public function has(string $attributeClass): bool
    {
        return null !== $this->last($attributeClass);
    }

    /**
     * Returns the most recently attached attribute compatible with the requested type.
     *
     * @template T of EnvelopeAttributeInterface
     *
     * @param class-string<T> $attributeClass
     *
     * @return T|null
     */
    public function last(string $attributeClass): ?EnvelopeAttributeInterface
    {
        for ($index = \count($this->attributes) - 1; $index >= 0; --$index) {
            $attribute = $this->attributes[$index];

            if ($attribute instanceof $attributeClass) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * Returns every attached attribute compatible with the requested type.
     *
     * @template T of EnvelopeAttributeInterface
     *
     * @param class-string<T> $attributeClass
     *
     * @return list<T>
     */
    public function all(string $attributeClass): array
    {
        return array_values(array_filter(
            $this->attributes,
            static fn (EnvelopeAttributeInterface $attribute): bool => $attribute instanceof $attributeClass,
        ));
    }

    /**
     * Returns a new envelope without attributes compatible with the requested type.
     *
     * @param class-string<EnvelopeAttributeInterface> $attributeClass
     */
    public function without(string $attributeClass): self
    {
        $attributes = array_values(array_filter(
            $this->attributes,
            static fn (EnvelopeAttributeInterface $attribute): bool => !$attribute instanceof $attributeClass,
        ));

        if ($attributes === $this->attributes) {
            return $this;
        }

        return new self($this->subject, $attributes);
    }

    /**
     * Returns every contextual attribute carried by this envelope.
     *
     * @return list<EnvelopeAttributeInterface>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
