<?php

declare(strict_types=1);

namespace App\Enveloping\DTO;

use App\Enveloping\Exception\EnvelopeTransportException;
use App\Enveloping\Validator\EnvelopeTransportPayloadValidator;
use App\Enveloping\Validator\EnvelopeTransportTypeValidator;

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
        if (!EnvelopeTransportTypeValidator::isValid($type)) {
            throw new EnvelopeTransportException('Envelope attribute transport type must match /^[a-z][a-z0-9._-]*$/.');
        }

        $this->payload = EnvelopeTransportPayloadValidator::normalizePayload($payload);
    }
}
