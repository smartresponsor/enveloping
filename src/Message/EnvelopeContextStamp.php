<?php

declare(strict_types=1);

namespace App\Enveloping\Message;

use App\Enveloping\DTO\EnvelopeAttributeTransportDTO;
use App\Enveloping\DTO\EnvelopeTransportDTO;
use App\Enveloping\Exception\EnvelopeTransportException;
use App\Enveloping\Validator\EnvelopeTransportPayloadValidator;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * Carries Enveloping execution context through Symfony Messenger without
 * changing the underlying business message.
 */
final readonly class EnvelopeContextStamp implements StampInterface
{
    /** @var list<array{type:non-empty-string,payload:array<string, mixed>}> */
    public array $attributes;

    /**
     * @param array<mixed> $attributes
     */
    public function __construct(
        array $attributes,
        public int $version = EnvelopeTransportDTO::CURRENT_VERSION,
    ) {
        if ($version < 1) {
            throw new EnvelopeTransportException('Envelope context stamp version must be positive.');
        }

        if (!array_is_list($attributes)) {
            throw new EnvelopeTransportException('Envelope context stamp attributes must be a list.');
        }

        $normalized = [];
        foreach ($attributes as $index => $attribute) {
            if (!\is_array($attribute)) {
                throw new EnvelopeTransportException(\sprintf('Envelope context stamp attribute %d must be an array.', $index));
            }

            $keys = array_keys($attribute);
            sort($keys);
            if (['payload', 'type'] !== $keys) {
                throw new EnvelopeTransportException(\sprintf('Envelope context stamp attribute %d must contain exactly type and payload.', $index));
            }

            $type = $attribute['type'] ?? null;
            $payload = $attribute['payload'] ?? null;

            if (!\is_string($type) || '' === $type) {
                throw new EnvelopeTransportException(\sprintf('Envelope context stamp attribute %d requires a non-empty type.', $index));
            }

            if (!\is_array($payload)) {
                throw new EnvelopeTransportException(\sprintf('Envelope context stamp attribute %d requires an array payload.', $index));
            }

            try {
                $payload = EnvelopeTransportPayloadValidator::normalizePayload($payload);
            } catch (EnvelopeTransportException $exception) {
                throw new EnvelopeTransportException(\sprintf('Envelope context stamp attribute %d has invalid payload: %s', $index, $exception->getMessage()), previous: $exception);
            }

            $normalized[] = [
                'type' => $type,
                'payload' => $payload,
            ];
        }

        $this->attributes = $normalized;
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
