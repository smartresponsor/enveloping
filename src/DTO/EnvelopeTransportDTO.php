<?php

declare(strict_types=1);

namespace App\Enveloping\DTO;

use App\Enveloping\Exception\EnvelopeTransportException;

/**
 * Carries an encoded subject plus typed envelope attribute payloads.
 *
 * The subject payload deliberately remains dynamic because its encoding belongs
 * to the composing application rather than Enveloping.
 */
final readonly class EnvelopeTransportDTO
{
    public const int CURRENT_VERSION = 1;

    /**
     * @param list<EnvelopeAttributeTransportDTO> $attributes
     */
    public function __construct(
        public mixed $subject,
        public array $attributes,
        public int $version = self::CURRENT_VERSION,
    ) {
        if ($version < 1) {
            throw new EnvelopeTransportException('Envelope transport version must be positive.');
        }
    }
}
