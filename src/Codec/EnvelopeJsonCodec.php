<?php

declare(strict_types=1);

namespace App\Enveloping\Codec;

use App\Enveloping\DTO\EnvelopeAttributeTransportDTO;
use App\Enveloping\DTO\EnvelopeTransportDTO;
use App\Enveloping\Validator\EnvelopeTransportPayloadValidator;
use App\Enveloping\ValueObject\Envelope;

/**
 * Encodes the versioned Envelope transport contract as JSON without owning
 * domain subject serialization.
 */
final readonly class EnvelopeJsonCodec
{
    public function __construct(private EnvelopeCodec $envelopeCodec)
    {
    }

    /**
     * @param callable(mixed): mixed $encodeSubject
     */
    public function encode(Envelope $envelope, callable $encodeSubject): string
    {
        $transport = $this->envelopeCodec->encode($envelope, $encodeSubject);
        EnvelopeTransportPayloadValidator::assertValue($transport->subject);

        return json_encode(
            [
                'version' => $transport->version,
                'subject' => $transport->subject,
                'attributes' => array_map(
                    static fn (EnvelopeAttributeTransportDTO $attribute): array => [
                        'type' => $attribute->type,
                        'payload' => $attribute->payload,
                    ],
                    $transport->attributes,
                ),
            ],
            \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION,
        );
    }

    /**
     * @param callable(mixed): mixed $decodeSubject
     */
    public function decode(string $json, callable $decodeSubject): Envelope
    {
        $decoded = json_decode($json, false, 512, \JSON_THROW_ON_ERROR);
        if (!$decoded instanceof \stdClass) {
            throw new \InvalidArgumentException('Envelope JSON document must be an object.');
        }

        $document = get_object_vars($decoded);

        $version = $document['version'] ?? null;
        if (!\is_int($version)) {
            throw new \InvalidArgumentException('Envelope JSON document requires an integer version.');
        }

        if (!\array_key_exists('subject', $document)) {
            throw new \InvalidArgumentException('Envelope JSON document requires a subject.');
        }

        $attributeRows = $document['attributes'] ?? null;
        if (!\is_array($attributeRows)) {
            throw new \InvalidArgumentException('Envelope JSON document requires an attributes list.');
        }

        $attributes = [];
        foreach ($attributeRows as $index => $row) {
            if (!$row instanceof \stdClass) {
                throw new \InvalidArgumentException(\sprintf('Envelope JSON attribute %d must be an object.', $index));
            }

            $attribute = get_object_vars($row);
            $type = $attribute['type'] ?? null;
            $payload = $attribute['payload'] ?? null;

            if (!\is_string($type) || '' === $type) {
                throw new \InvalidArgumentException(\sprintf('Envelope JSON attribute %d requires a non-empty type.', $index));
            }

            if (!$payload instanceof \stdClass) {
                throw new \InvalidArgumentException(\sprintf('Envelope JSON attribute %d requires an object payload.', $index));
            }

            $attributes[] = new EnvelopeAttributeTransportDTO(
                $type,
                $this->normalizeJsonObject($payload),
            );
        }

        return $this->envelopeCodec->decode(
            new EnvelopeTransportDTO(
                $this->normalizeJsonValue($document['subject']),
                $attributes,
                $version,
            ),
            $decodeSubject,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeJsonObject(\stdClass $value): array
    {
        $normalized = [];
        /** @var array<string, mixed> $properties */
        $properties = get_object_vars($value);

        foreach ($properties as $key => $item) {
            $normalized[$key] = $this->normalizeJsonValue($item);
        }

        return $normalized;
    }

    private function normalizeJsonValue(mixed $value): mixed
    {
        if ($value instanceof \stdClass) {
            return $this->normalizeJsonObject($value);
        }

        if (\is_array($value)) {
            return array_map(
                fn (mixed $item): mixed => $this->normalizeJsonValue($item),
                $value,
            );
        }

        return $value;
    }
}
