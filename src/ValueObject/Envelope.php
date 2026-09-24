<?php

declare(strict_types=1);

namespace App\Enveloping\ValueObject;

use App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface;

/**
 * Wraps an arbitrary subject with immutable typed execution-context values.
 *
 * The subject remains unaware of Enveloping and its intrinsic state is never
 * mutated when contextual attributes are added or removed.
 */
final readonly class Envelope
{
    /** @var array<class-string<EnvelopeAttributeInterface>, list<EnvelopeAttributeInterface>> */
    private array $attributes;

    /** @param iterable<EnvelopeAttributeInterface> $attributes */
    public function __construct(public mixed $subject, iterable $attributes = [])
    {
        $indexed = [];

        foreach ($attributes as $attribute) {
            $indexed[$attribute::class][] = $attribute;
        }

        $this->attributes = $indexed;
    }

    /**
     * Returns a new envelope with additional typed contextual attributes.
     */
    public function with(EnvelopeAttributeInterface ...$attributes): self
    {
        if ([] === $attributes) {
            return $this;
        }

        return new self($this->subject, [...$this->flatten(), ...$attributes]);
    }

    /**
     * Returns the most recently attached attribute of the requested type.
     *
     * @template T of EnvelopeAttributeInterface
     *
     * @param class-string<T> $attributeClass
     *
     * @return T|null
     */
    public function last(string $attributeClass): ?EnvelopeAttributeInterface
    {
        $attributes = $this->attributes[$attributeClass] ?? [];
        if ([] === $attributes) {
            return null;
        }

        $last = $attributes[array_key_last($attributes)];

        return $last instanceof $attributeClass ? $last : null;
    }

    /**
     * Returns every attached attribute of the requested type in attachment order.
     *
     * @template T of EnvelopeAttributeInterface
     *
     * @param class-string<T> $attributeClass
     *
     * @return list<T>
     */
    public function all(string $attributeClass): array
    {
        $matches = $this->attributes[$attributeClass] ?? [];

        return array_values(array_filter(
            $matches,
            static fn (EnvelopeAttributeInterface $attribute): bool => $attribute instanceof $attributeClass,
        ));
    }

    /**
     * Returns a new envelope without attributes of the requested type.
     *
     * @param class-string<EnvelopeAttributeInterface> $attributeClass
     */
    public function without(string $attributeClass): self
    {
        $attributes = [];

        foreach ($this->attributes as $class => $items) {
            if ($class === $attributeClass) {
                continue;
            }

            array_push($attributes, ...$items);
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
        return $this->flatten();
    }

    /** @return list<EnvelopeAttributeInterface> */
    private function flatten(): array
    {
        $flat = [];

        foreach ($this->attributes as $attributes) {
            array_push($flat, ...$attributes);
        }

        return $flat;
    }
}
