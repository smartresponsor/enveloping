<?php

declare(strict_types=1);

namespace App\Enveloping\ValueObject;

use App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface;

/**
 * Identifies the actor associated with one enveloped execution context.
 */
final readonly class EnvelopeActorAttribute implements EnvelopeAttributeInterface
{
    public function __construct(public string $identity)
    {
    }
}
