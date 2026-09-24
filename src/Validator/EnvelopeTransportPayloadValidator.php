<?php

declare(strict_types=1);

namespace App\Enveloping\Validator;

use App\Enveloping\Exception\EnvelopeTransportException;

/**
 * Validates transport payloads against JSON-safe recursive value semantics.
 */
final class EnvelopeTransportPayloadValidator
{
    private const int MAX_DEPTH = 512;

    /**
     * @param array<mixed, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public static function normalizePayload(array $payload): array
    {
        $normalized = [];

        foreach ($payload as $key => $value) {
            if (!\is_string($key)) {
                throw new EnvelopeTransportException('Envelope transport payload map keys must be strings.');
            }

            self::assertValue($value);
            $normalized[$key] = $value;
        }

        return $normalized;
    }

    /**
     * Validates one arbitrary JSON-safe transport value.
     */
    public static function assertValue(mixed $value, int $depth = 0): void
    {
        if (null === $value || \is_bool($value) || \is_int($value)) {
            return;
        }

        if (\is_float($value)) {
            if (!is_finite($value)) {
                throw new EnvelopeTransportException('Envelope transport payload floats must be finite.');
            }

            return;
        }

        if (\is_string($value)) {
            if (1 !== preg_match('//u', $value)) {
                throw new EnvelopeTransportException('Envelope transport payload strings must be valid UTF-8.');
            }

            return;
        }

        if (!\is_array($value)) {
            throw new EnvelopeTransportException('Envelope transport payload values must be JSON-safe.');
        }

        if ($depth >= self::MAX_DEPTH) {
            throw new EnvelopeTransportException(\sprintf('Envelope transport payload nesting must not exceed %d levels.', self::MAX_DEPTH));
        }

        if (array_is_list($value)) {
            foreach ($value as $item) {
                self::assertValue($item, $depth + 1);
            }

            return;
        }

        foreach ($value as $key => $item) {
            if (!\is_string($key)) {
                throw new EnvelopeTransportException('Envelope transport payload map keys must be strings.');
            }

            self::assertValue($item, $depth + 1);
        }
    }
}
