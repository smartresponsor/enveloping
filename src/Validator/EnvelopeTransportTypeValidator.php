<?php

declare(strict_types=1);

namespace App\Enveloping\Validator;

/**
 * Validates stable Envelope wire type identifiers.
 */
final class EnvelopeTransportTypeValidator
{
    /** @phpstan-assert-if-true non-empty-string $type */
    public static function isValid(string $type): bool
    {
        return 1 === preg_match('/^[a-z][a-z0-9._-]*$/', $type);
    }
}
