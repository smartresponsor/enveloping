<?php

declare(strict_types=1);

namespace App\Enveloping\DTO;

/**
 * Carries an encoded subject plus typed envelope attribute payloads.
 *
 * The subject payload deliberately remains dynamic because its encoding belongs
 * to the composing application rather than Enveloping.
 */
final readonly class EnvelopeTransportDTO
{
    /**
     * @param list<EnvelopeAttributeTransportDTO> $attributes
     */
    public function __construct(
        public mixed $subject,
        public array $attributes,
    ) {
    }
}
