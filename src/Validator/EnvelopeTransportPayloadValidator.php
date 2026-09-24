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
        /** @var list<array{mixed, int}> $pending */
        $pending = [[$value, $depth]];

        while ([] !== $pending) {
            [$current, $currentDepth] = array_pop($pending);

            if (null === $current || \is_bool($current) || \is_int($current)) {
                continue;
            }

            if (\is_float($current)) {
                if (!is_finite($current)) {
                    throw new EnvelopeTransportException('Envelope transport payload floats must be finite.');
                }

                continue;
            }

            if (\is_string($current)) {
                if (1 !== preg_match('//u', $current)) {
                    throw new EnvelopeTransportException('Envelope transport payload strings must be valid UTF-8.');
                }

                continue;
            }

            if (!\is_array($current)) {
                throw new EnvelopeTransportException('Envelope transport payload values must be JSON-safe.');
            }

            if ($currentDepth >= self::MAX_DEPTH) {
                throw new EnvelopeTransportException(\sprintf('Envelope transport payload nesting must not exceed %d levels.', self::MAX_DEPTH));
            }

            if (array_is_list($current)) {
                foreach ($current as $item) {
                    $pending[] = [$item, $currentDepth + 1];
                }

                continue;
            }

            foreach ($current as $key => $item) {
                if (!\is_string($key)) {
                    throw new EnvelopeTransportException('Envelope transport payload map keys must be strings.');
                }

                $pending[] = [$item, $currentDepth + 1];
            }
        }
    }
}
