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

        self::assertSame('test', $transport->type);
        self::assertSame('custom-value', $transport->payload['value']);
        self::assertInstanceOf(EnvelopeTestAttribute::class, $decoded);
        self::assertSame('custom-value', $decoded->value);
    }

    public function testDuplicateTransportTypeOwnershipIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('owned by multiple codecs');

        new EnvelopeAttributeCodecRegistry([
            new EnvelopeTestAttributeCodec(),
            new EnvelopeTestAttributeCodec(),
        ]);
    }

    public function testEmptyTransportTypeOwnershipIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('transport type must be non-empty');

        new EnvelopeAttributeCodecRegistry([
            new EnvelopeEmptyTypeCodec(),
        ]);
    }

    public function testRuntimeAttributeOwnershipMustBeUnique(): void
    {
        $registry = new EnvelopeAttributeCodecRegistry([
            new EnvelopeTestAttributeCodec(),
            new EnvelopeSecondRuntimeOwnerCodec(),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('supported by multiple codecs');

        $registry->encode(new EnvelopeTestAttribute('value'));
    }

    public function testCodecCannotEmitUndeclaredTransportType(): void
    {
        $registry = new EnvelopeAttributeCodecRegistry([
            new EnvelopeUndeclaredOutputCodec(),
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('emitted undeclared transport type');

        $registry->encode(new EnvelopeTestAttribute('value'));
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

    public function testTransportDTOAcceptsRecursiveJsonSafePayload(): void
    {
        $transport = new EnvelopeAttributeTransportDTO('nested', [
            'map' => [
                'enabled' => true,
                'threshold' => 1.5,
                'nested' => ['value' => 'ok'],
            ],
            'list' => [1, 'two', null, ['three' => 3]],
        ]);

        self::assertSame([
            'map' => [
                'enabled' => true,
                'threshold' => 1.5,
                'nested' => ['value' => 'ok'],
            ],
            'list' => [1, 'two', null, ['three' => 3]],
        ], $transport->payload);
    }

    public function testTransportDTORejectsNonJsonSafePayload(): void
    {
        $cases = [
            ['object' => new \stdClass()],
            ['sparse-map' => [1 => 'numeric-key']],
        ];

        foreach ($cases as $payload) {
            try {
                new EnvelopeAttributeTransportDTO('invalid', $payload);
                self::fail('Non JSON-safe transport payload should be rejected.');
            } catch (\InvalidArgumentException) {
                self::addToAssertionCount(1);
            }
        }
    }

    public function testTransportDTORejectsEmptyAttributeType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new EnvelopeAttributeTransportDTO('', []);
    }

    public function testTransportFormatVersionIsStableAndUnsupportedVersionsAreRejected(): void
    {
        $codec = new EnvelopeCodec(new EnvelopeAttributeCodecRegistry([
            new EnvelopeBuiltInAttributeCodec(),
        ]));

        $transport = $codec->encode(new Envelope('subject'), static fn (mixed $value): mixed => $value);

        self::assertSame(1, $transport->version);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported envelope transport version 2');

        $codec->decode(
            new \App\Enveloping\DTO\EnvelopeTransportDTO('subject', [], 2),
            static fn (mixed $value): mixed => $value,
        );
    }

    public function testTransportVersionMustBePositive(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new \App\Enveloping\DTO\EnvelopeTransportDTO('subject', [], 0);
    }

    public function testBuiltInCodecRejectsMalformedPayloadAndUnknownTypes(): void
    {
        $codec = new EnvelopeBuiltInAttributeCodec();

        try {
            $codec->decode(new EnvelopeAttributeTransportDTO('actor', ['identity' => 42]));
            self::fail('Malformed built-in payload should be rejected.');
        } catch (\InvalidArgumentException $exception) {
            self::assertStringContainsString('requires string payload key identity', $exception->getMessage());
        }

        $unknown = new EnvelopeAttributeTransportDTO('unknown.attribute', []);

        self::assertNotContains($unknown->type, $codec->transportTypes());

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
    public function supportsAttribute(EnvelopeAttributeInterface $attribute): bool
    {
        return $attribute instanceof EnvelopeTestAttribute;
    }

    public function transportTypes(): array
    {
        return ['test'];
    }

    public function encode(EnvelopeAttributeInterface $attribute): EnvelopeAttributeTransportDTO
    {
        if (!$attribute instanceof EnvelopeTestAttribute) {
            throw new \InvalidArgumentException('EnvelopeTestAttributeCodec received an unsupported attribute.');
        }

        return new EnvelopeAttributeTransportDTO('test', ['value' => $attribute->value]);
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

final class EnvelopeEmptyTypeCodec implements EnvelopeAttributeCodec
{
    public function supportsAttribute(EnvelopeAttributeInterface $attribute): bool
    {
        return false;
    }

    public function transportTypes(): array
    {
        return [''];
    }

    public function encode(EnvelopeAttributeInterface $attribute): EnvelopeAttributeTransportDTO
    {
        throw new \LogicException('Not used.');
    }

    public function decode(EnvelopeAttributeTransportDTO $transport): EnvelopeAttributeInterface
    {
        throw new \LogicException('Not used.');
    }
}

final class EnvelopeSecondRuntimeOwnerCodec implements EnvelopeAttributeCodec
{
    public function supportsAttribute(EnvelopeAttributeInterface $attribute): bool
    {
        return $attribute instanceof EnvelopeTestAttribute;
    }

    public function transportTypes(): array
    {
        return ['second-test'];
    }

    public function encode(EnvelopeAttributeInterface $attribute): EnvelopeAttributeTransportDTO
    {
        return new EnvelopeAttributeTransportDTO('second-test', ['value' => 'unused']);
    }

    public function decode(EnvelopeAttributeTransportDTO $transport): EnvelopeAttributeInterface
    {
        return new EnvelopeTestAttribute('unused');
    }
}

final class EnvelopeUndeclaredOutputCodec implements EnvelopeAttributeCodec
{
    public function supportsAttribute(EnvelopeAttributeInterface $attribute): bool
    {
        return $attribute instanceof EnvelopeTestAttribute;
    }

    public function transportTypes(): array
    {
        return ['declared'];
    }

    public function encode(EnvelopeAttributeInterface $attribute): EnvelopeAttributeTransportDTO
    {
        return new EnvelopeAttributeTransportDTO('undeclared', ['value' => 'value']);
    }

    public function decode(EnvelopeAttributeTransportDTO $transport): EnvelopeAttributeInterface
    {
        return new EnvelopeTestAttribute('unused');
    }
}
