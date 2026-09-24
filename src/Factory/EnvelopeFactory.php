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
     * Wraps a subject with the contextual attributes supplied by the composing layer.
     */
    public function create(mixed $subject, EnvelopeAttributeInterface ...$attributes): Envelope
    {
        return new Envelope($subject, $attributes);
    }
}
