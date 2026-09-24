<?php

declare(strict_types=1);

namespace App\Enveloping\Factory;

use App\Enveloping\AttributeInterface\EnvelopeAttributeInterface;
use App\Enveloping\Envelope\Envelope;

final class EnvelopeFactory
{
    public function create(mixed $subject, EnvelopeAttributeInterface ...$attributes): Envelope
    {
        return new Envelope($subject, $attributes);
    }
}
