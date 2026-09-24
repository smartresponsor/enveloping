<?php

declare(strict_types=1);

namespace App\Enveloping\DTO;

/**
 * Carries one encoded envelope attribute across a serialization boundary.
 */
final readonly class EnvelopeAttributeTransportDTO
{
    /**
     * @param array<string, scalar|null> $payload
     */
    public function __construct(
        public string $type,
        public array $payload,
    ) {
        if ('' === $type) {
            throw new \InvalidArgumentException('Envelope attribute transport type must be non-empty.');
        }
    }
}
