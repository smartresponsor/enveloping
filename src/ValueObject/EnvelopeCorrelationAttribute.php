<?php

declare(strict_types=1);

namespace App\Enveloping\ValueObject;

use App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface;

/**
 * Correlates related enveloped executions across component and transport boundaries.
 */
final readonly class EnvelopeCorrelationAttribute implements EnvelopeAttributeInterface
{
    public function __construct(public string $id)
    {
    }
}
