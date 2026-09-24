<?php

declare(strict_types=1);

namespace App\Enveloping\DTO;

use App\Enveloping\Exception\EnvelopeTransportException;
use App\Enveloping\Validator\EnvelopeTransportPayloadValidator;

/**
 * Carries one encoded envelope attribute across a serialization boundary.
 */
final readonly class EnvelopeAttributeTransportDTO
{
    /** @var array<string, mixed> */
    public array $payload;

    /**
     * @param array<mixed, mixed> $payload
     */
    public function __construct(
        public string $type,
        array $payload,
    ) {
        if ('' === $type) {
            throw new EnvelopeTransportException('Envelope attribute transport type must be non-empty.');
        }

        $this->payload = EnvelopeTransportPayloadValidator::normalizePayload($payload);
    }
}
