<?php

declare(strict_types=1);

namespace App\Enveloping\ValueObject;

use App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface;

/**
 * Identifies the direct cause of one enveloped operation or event.
 */
final readonly class EnvelopeCausationAttribute implements EnvelopeAttributeInterface
{
    public function __construct(public string $id)
    {
        if ('' === $id) {
            throw new \InvalidArgumentException('Causation identifier must be non-empty.');
        }
    }
}
