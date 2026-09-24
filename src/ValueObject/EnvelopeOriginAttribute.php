<?php

declare(strict_types=1);

namespace App\Enveloping\ValueObject;

use App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface;

/**
 * Records the execution source that originated an enveloped interaction.
 */
final readonly class EnvelopeOriginAttribute implements EnvelopeAttributeInterface
{
    public function __construct(public string $source)
    {
    }
}
