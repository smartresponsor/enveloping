<?php

declare(strict_types=1);

namespace App\Enveloping\Attribute;

use App\Enveloping\AttributeInterface\EnvelopeAttributeInterface;

final readonly class ActorAttribute implements EnvelopeAttributeInterface
{
    public function __construct(public string $identity)
    {
    }
}
