<?php

declare(strict_types=1);

namespace App\Enveloping\Tests\Unit;

use App\Enveloping\Codec\EnvelopeAttributeCodec;
use App\Enveloping\Codec\EnvelopeBuiltInAttributeCodec;
use App\Enveloping\Codec\EnvelopeCodec;
use App\Enveloping\DTO\EnvelopeAttributeTransportDTO;
use App\Enveloping\Registry\EnvelopeAttributeCodecRegistry;
use App\Enveloping\ValueObject\Envelope;
use App\Enveloping\ValueObject\EnvelopeActorAttribute;
use App\Enveloping\ValueObject\EnvelopeCorrelationAttribute;
use App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface;
use PHPUnit\Framework\TestCase;

final class EnvelopeCodecTest extends TestCase
{
    public function testBuiltInContextRoundTripsWhileSubjectCodecStaysExternal(): void
    {
        $codec = new EnvelopeCodec(new EnvelopeAttributeCodecRegistry([
            new EnvelopeBuiltInAttributeCodec(),
        ]));

        $envelope = new Envelope(['id' => 'shipment-42'], [
            new EnvelopeActorAttribute('actor-1'),
            new EnvelopeCorrelationAttribute('corr-1'),
        ]);

        $transport = $codec->encode(
            $envelope,
            static fn (mixed $value): mixed => $value,
        );

        self::assertSame(['id' => 'shipment-42'], $transport->subject);
        self::assertCount(2, $transport->attributes);

        $decoded = $codec->decode(
            $transport,
            static fn (mixed $value): mixed => $value,
        );

        self::assertSame(['id' => 'shipment-42'], $decoded->subject);
        self::assertSame('actor-1', $decoded->last(EnvelopeActorAttribute::class)?->identity);
        self::assertSame('corr-1', $decoded->last(EnvelopeCorrelationAttribute::class)?->id);
    }

    public function testCustomAttributeCodecCanBeComposedWithoutChangingEnvelopeCore(): void
    {
        $registry = new EnvelopeAttributeCodecRegistry([
            new EnvelopeTestAttributeCodec(),
        ]);
        $attribute = new EnvelopeTestAttribute('custom-value');

        $transport = $registry->encode($attribute);
        $decoded = $registry->decode($transport);

        self::assertSame(EnvelopeTestAttribute::class, $transport->type);
        self::assertSame('custom-value', $transport->payload['value']);
        self::assertInstanceOf(EnvelopeTestAttribute::class, $decoded);
        self::assertSame('custom-value', $decoded->value);
    }

    public function testUnsupportedAttributeFailsAtSerializationBoundary(): void
    {
        $registry = new EnvelopeAttributeCodecRegistry([]);
        $attribute = new class implements EnvelopeAttributeInterface {
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No envelope attribute codec supports');

        $registry->encode($attribute);
    }

    public function testTransportDTORejectsEmptyAttributeType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new EnvelopeAttributeTransportDTO('', []);
    }

    public function testBuiltInCodecRejectsMalformedPayloadAndUnknownTypes(): void
    {
        $codec = new EnvelopeBuiltInAttributeCodec();

        try {
            $codec->decode(new EnvelopeAttributeTransportDTO(EnvelopeActorAttribute::class, ['identity' => 42]));
            self::fail('Malformed built-in payload should be rejected.');
        } catch (\InvalidArgumentException $exception) {
            self::assertStringContainsString('requires string payload key identity', $exception->getMessage());
        }

        $unknown = new EnvelopeAttributeTransportDTO('unknown.attribute', []);

        self::assertFalse($codec->supports($unknown->type));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported envelope attribute transport type');

        $codec->decode($unknown);
    }
}

final readonly class EnvelopeTestAttribute implements EnvelopeAttributeInterface
{
    public function __construct(public string $value)
    {
    }
}

final class EnvelopeTestAttributeCodec implements EnvelopeAttributeCodec
{
    public function supports(EnvelopeAttributeInterface|string $attribute): bool
    {
        return (\is_string($attribute) ? $attribute : $attribute::class) === EnvelopeTestAttribute::class;
    }

    public function encode(EnvelopeAttributeInterface $attribute): EnvelopeAttributeTransportDTO
    {
        if (!$attribute instanceof EnvelopeTestAttribute) {
            throw new \InvalidArgumentException('EnvelopeTestAttributeCodec received an unsupported attribute.');
        }

        return new EnvelopeAttributeTransportDTO(EnvelopeTestAttribute::class, ['value' => $attribute->value]);
    }

    public function decode(EnvelopeAttributeTransportDTO $transport): EnvelopeAttributeInterface
    {
        $value = $transport->payload['value'] ?? null;
        if (!\is_string($value)) {
            throw new \InvalidArgumentException('EnvelopeTestAttribute payload value must be a string.');
        }

        return new EnvelopeTestAttribute($value);
    }
}
