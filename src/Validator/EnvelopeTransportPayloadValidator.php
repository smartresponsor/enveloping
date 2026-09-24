<?php

declare(strict_types=1);

namespace App\Enveloping\Validator;

use App\Enveloping\Exception\EnvelopeTransportException;

/**
 * Validates transport payloads against JSON-safe recursive value semantics.
 */
final class EnvelopeTransportPayloadValidator
{
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
    public static function assertValue(mixed $value): void
    {
        if (null === $value || \is_scalar($value)) {
            return;
        }

        if (!\is_array($value)) {
            throw new EnvelopeTransportException('Envelope transport payload values must be JSON-safe.');
        }

        if (array_is_list($value)) {
            foreach ($value as $item) {
                self::assertValue($item);
            }

            return;
        }

        foreach ($value as $key => $item) {
            if (!\is_string($key)) {
                throw new EnvelopeTransportException('Envelope transport payload map keys must be strings.');
            }

            self::assertValue($item);
        }
    }
}
