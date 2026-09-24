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

    /** @var list<EnvelopeAttributeTransportDTO> */
    public array $attributes;

    /** @param array<mixed> $attributes */
    public function __construct(
        public mixed $subject,
        array $attributes,
        public int $version = self::CURRENT_VERSION,
    ) {
        if ($version < 1) {
            throw new EnvelopeTransportException('Envelope transport version must be positive.');
        }

        if (!array_is_list($attributes)) {
            throw new EnvelopeTransportException('Envelope transport attributes must be a list.');
        }

        $normalized = [];
        foreach ($attributes as $index => $attribute) {
            if (!$attribute instanceof EnvelopeAttributeTransportDTO) {
                throw new EnvelopeTransportException(\sprintf('Envelope transport attribute %d must be an EnvelopeAttributeTransportDTO.', $index));
            }

            $normalized[] = $attribute;
        }

        $this->attributes = $normalized;
    }
}
