<?php

declare(strict_types=1);

namespace App\Enveloping\Message;

use App\Enveloping\DTO\EnvelopeAttributeTransportDTO;
use App\Enveloping\DTO\EnvelopeTransportDTO;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * Carries Enveloping execution context through Symfony Messenger without
 * changing the underlying business message.
 */
final readonly class EnvelopeContextStamp implements StampInterface
{
    /**
     * @param list<array{type:string,payload:array<string, scalar|null>}> $attributes
     */
    public function __construct(
        public array $attributes,
        public int $version = EnvelopeTransportDTO::CURRENT_VERSION,
    ) {
        if ($version < 1) {
            throw new \InvalidArgumentException('Envelope context stamp version must be positive.');
        }
    }

    /**
     * @param list<EnvelopeAttributeTransportDTO> $attributes
     */
    public static function fromTransportAttributes(array $attributes, int $version): self
    {
        return new self(
            array_map(
                static fn (EnvelopeAttributeTransportDTO $attribute): array => [
                    'type' => $attribute->type,
                    'payload' => $attribute->payload,
                ],
                $attributes,
            ),
            $version,
        );
    }

    /**
     * @return list<EnvelopeAttributeTransportDTO>
     */
    public function transportAttributes(): array
    {
        return array_map(
            static fn (array $attribute): EnvelopeAttributeTransportDTO => new EnvelopeAttributeTransportDTO(
                $attribute['type'],
                $attribute['payload'],
            ),
            $this->attributes,
        );
    }
}
