<?php

declare(strict_types=1);

namespace App\Enveloping\Tests\Unit;

use App\Enveloping\Codec\EnvelopeAttributeCodec;
use App\Enveloping\Codec\EnvelopeBuiltInAttributeCodec;
use App\Enveloping\Codec\EnvelopeCodec;
use App\Enveloping\DTO\EnvelopeAttributeTransportDTO;
use App\Enveloping\Exception\EnvelopeCodecException;
use App\Enveloping\Exception\EnvelopeTransportException;
use App\Enveloping\Registry\EnvelopeAttributeCodecRegistry;
use App\Enveloping\ValueObject\Envelope;
use App\Enveloping\ValueObject\EnvelopeActorAttribute;
use App\Enveloping\ValueObject\EnvelopeCausationAttribute;
use App\Enveloping\ValueObject\EnvelopeCorrelationAttribute;
use App\Enveloping\ValueObject\EnvelopeOriginAttribute;
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

    public function testCodecRegistryRejectsNonCodecItems(): void
    {
        $this->expectException(EnvelopeCodecException::class);
        $this->expectExceptionMessage('registry items must implement');

        new EnvelopeAttributeCodecRegistry(['not-a-codec']);
    }

    public function testCodecRegistryRejectsAssociativeTransportTypeCollection(): void
    {
        $this->expectException(EnvelopeCodecException::class);
        $this->expectExceptionMessage('transport types must be a list');

        new EnvelopeAttributeCodecRegistry([
            new EnvelopeAssociativeTransportTypesCodec(),
        ]);
    }

    public function testCodecRegistryRejectsNonStringTransportType(): void
    {
        $this->expectException(EnvelopeCodecException::class);
        $this->expectExceptionMessage('transport type must match');

        new EnvelopeAttributeCodecRegistry([
            new EnvelopeNonStringTransportTypesCodec(),
        ]);
    }

    public function testDuplicateTransportTypeOwnershipIsRejected(): void
    {
        $this->expectException(EnvelopeCodecException::class);
        $this->expectExceptionMessage('owned by multiple codecs');

        new EnvelopeAttributeCodecRegistry([
            new EnvelopeTestAttributeCodec(),
            new EnvelopeTestAttributeCodec(),
        ]);
    }

    public function testEmptyTransportTypeOwnershipIsRejected(): void
    {
        $this->expectException(EnvelopeCodecException::class);
        $this->expectExceptionMessage('transport type must match');

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

        $this->expectException(EnvelopeCodecException::class);
        $this->expectExceptionMessage('supported by multiple codecs');

        $registry->encode(new EnvelopeTestAttribute('value'));
    }

    public function testCodecCannotEmitUndeclaredTransportType(): void
    {
        $registry = new EnvelopeAttributeCodecRegistry([
            new EnvelopeUndeclaredOutputCodec(),
        ]);

        $this->expectException(EnvelopeCodecException::class);
        $this->expectExceptionMessage('emitted undeclared transport type');

        $registry->encode(new EnvelopeTestAttribute('value'));
    }

    public function testDecodeRejectsRuntimeAttributeNotOwnedByWireCodec(): void
    {
        $registry = new EnvelopeAttributeCodecRegistry([
            new EnvelopeBadDecodeCodec(),
        ]);

        $this->expectException(EnvelopeCodecException::class);
        $this->expectExceptionMessage('decoded transport type bad into an unsupported runtime attribute');

        $registry->decode(new EnvelopeAttributeTransportDTO('bad', ['value' => 'x']));
    }

    public function testDecodeRejectsAmbiguousRuntimeAttributeOwnership(): void
    {
        $registry = new EnvelopeAttributeCodecRegistry([
            new EnvelopeTestAttributeCodec(),
            new EnvelopeSecondRuntimeOwnerCodec(),
        ]);

        $this->expectException(EnvelopeCodecException::class);
        $this->expectExceptionMessage('Decoded Envelope attribute');

        $registry->decode(new EnvelopeAttributeTransportDTO('test', ['value' => 'x']));
    }

    public function testUnsupportedAttributeFailsAtSerializationBoundary(): void
    {
        $registry = new EnvelopeAttributeCodecRegistry([]);
        $attribute = new class implements EnvelopeAttributeInterface {
        };

        $this->expectException(EnvelopeCodecException::class);
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
            } catch (EnvelopeTransportException) {
                self::addToAssertionCount(1);
            }
        }
    }

    public function testTransportPayloadRejectsNonFiniteFloats(): void
    {
        foreach ([\NAN, \INF, -\INF] as $value) {
            try {
                new EnvelopeAttributeTransportDTO('invalid', ['value' => $value]);
                self::fail('Non-finite floats must be rejected.');
            } catch (EnvelopeTransportException $exception) {
                self::assertStringContainsString('floats must be finite', $exception->getMessage());
            }
        }
    }

    public function testTransportPayloadRejectsInvalidUtf8Strings(): void
    {
        $this->expectException(EnvelopeTransportException::class);
        $this->expectExceptionMessage('valid UTF-8');

        new EnvelopeAttributeTransportDTO('invalid', ['value' => "\xB1\x31"]);
    }

    public function testTransportPayloadRejectsExcessiveNesting(): void
    {
        $value = 'leaf';
        for ($index = 0; $index < 513; ++$index) {
            $value = [$value];
        }

        $this->expectException(EnvelopeTransportException::class);
        $this->expectExceptionMessage('nesting must not exceed 512 levels');

        new EnvelopeAttributeTransportDTO('invalid', ['value' => $value]);
    }

    public function testTransportDTORejectsInvalidWireTypeGrammar(): void
    {
        foreach (['', ' Actor', 'Actor', 'actor/type', 'actor type', 'actor:tag'] as $type) {
            try {
                new EnvelopeAttributeTransportDTO($type, []);
                self::fail('Invalid wire type must be rejected.');
            } catch (EnvelopeTransportException $exception) {
                self::assertStringContainsString('transport type must match', $exception->getMessage());
            }
        }
    }

    public function testTransportDTORejectsEmptyAttributeType(): void
    {
        $this->expectException(EnvelopeTransportException::class);

        new EnvelopeAttributeTransportDTO('', []);
    }

    public function testTransportFormatVersionIsStableAndUnsupportedVersionsAreRejected(): void
    {
        $codec = new EnvelopeCodec(new EnvelopeAttributeCodecRegistry([
            new EnvelopeBuiltInAttributeCodec(),
        ]));

        $transport = $codec->encode(new Envelope('subject'), static fn (mixed $value): mixed => $value);

        self::assertSame(1, $transport->version);

        $this->expectException(EnvelopeTransportException::class);
        $this->expectExceptionMessage('Unsupported envelope transport version 2');

        $codec->decode(
            new \App\Enveloping\DTO\EnvelopeTransportDTO('subject', [], 2),
            static fn (mixed $value): mixed => $value,
        );
    }

    public function testTransportDTORejectsAssociativeAttributeCollection(): void
    {
        $this->expectException(EnvelopeTransportException::class);
        $this->expectExceptionMessage('attributes must be a list');

        new \App\Enveloping\DTO\EnvelopeTransportDTO(
            'subject',
            ['attribute' => new EnvelopeAttributeTransportDTO('actor', ['identity' => 'actor-1'])],
        );
    }

    public function testTransportDTORejectsNonAttributeDTOItems(): void
    {
        $this->expectException(EnvelopeTransportException::class);
        $this->expectExceptionMessage('must be an EnvelopeAttributeTransportDTO');

        new \App\Enveloping\DTO\EnvelopeTransportDTO(
            'subject',
            ['not-an-attribute-dto'],
        );
    }

    public function testTransportVersionMustBePositive(): void
    {
        $this->expectException(EnvelopeTransportException::class);

        new \App\Enveloping\DTO\EnvelopeTransportDTO('subject', [], 0);
    }

    public function testBuiltInCodecCoversItsCompleteOwnedVocabulary(): void
    {
        $codec = new EnvelopeBuiltInAttributeCodec();
        $attributes = [
            new EnvelopeActorAttribute('actor-1'),
            new EnvelopeOriginAttribute('origin-1'),
            new EnvelopeCorrelationAttribute('correlation-1'),
            new EnvelopeCausationAttribute('causation-1'),
        ];

        self::assertSame(['actor', 'causation', 'correlation', 'origin'], $codec->transportTypes());

        foreach ($attributes as $attribute) {
            self::assertTrue($codec->supportsAttribute($attribute));

            $transport = $codec->encode($attribute);
            $decoded = $codec->decode($transport);

            self::assertSame($attribute::class, $decoded::class);
        }

        $unsupported = new EnvelopeTestAttribute('unsupported');
        self::assertFalse($codec->supportsAttribute($unsupported));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported envelope attribute');

        $codec->encode($unsupported);
    }

    public function testBuiltInCodecRejectsMalformedPayloadAndUnknownTypes(): void
    {
        $codec = new EnvelopeBuiltInAttributeCodec();

        try {
            $codec->decode(new EnvelopeAttributeTransportDTO('actor', [
                'identity' => 'actor-1',
                'extra' => true,
            ]));
            self::fail('Unexpected built-in payload fields should be rejected.');
        } catch (EnvelopeTransportException $exception) {
            self::assertStringContainsString('exactly key identity', $exception->getMessage());
        }

        try {
            $codec->decode(new EnvelopeAttributeTransportDTO('actor', ['identity' => 42]));
            self::fail('Malformed built-in payload should be rejected.');
        } catch (EnvelopeTransportException $exception) {
            self::assertStringContainsString('requires string payload key identity', $exception->getMessage());
        }

        $unknown = new EnvelopeAttributeTransportDTO('unknown.attribute', []);

        self::assertNotContains($unknown->type, $codec->transportTypes());

        $this->expectException(EnvelopeTransportException::class);
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

final class EnvelopeAssociativeTransportTypesCodec implements EnvelopeAttributeCodec
{
    public function supportsAttribute(EnvelopeAttributeInterface $attribute): bool
    {
        return false;
    }

    public function transportTypes(): array
    {
        // @phpstan-ignore-next-line deliberate malformed runtime contract
        return ['type' => 'test'];
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

final class EnvelopeNonStringTransportTypesCodec implements EnvelopeAttributeCodec
{
    public function supportsAttribute(EnvelopeAttributeInterface $attribute): bool
    {
        return false;
    }

    public function transportTypes(): array
    {
        // @phpstan-ignore-next-line deliberate malformed runtime contract
        return [42];
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

final readonly class EnvelopeOtherAttribute implements EnvelopeAttributeInterface
{
    public function __construct(public string $value)
    {
    }
}

final class EnvelopeBadDecodeCodec implements EnvelopeAttributeCodec
{
    public function supportsAttribute(EnvelopeAttributeInterface $attribute): bool
    {
        return $attribute instanceof EnvelopeTestAttribute;
    }

    public function transportTypes(): array
    {
        return ['bad'];
    }

    public function encode(EnvelopeAttributeInterface $attribute): EnvelopeAttributeTransportDTO
    {
        return new EnvelopeAttributeTransportDTO('bad', ['value' => 'x']);
    }

    public function decode(EnvelopeAttributeTransportDTO $transport): EnvelopeAttributeInterface
    {
        $value = $transport->payload['value'] ?? null;
        if (!\is_string($value)) {
            throw new \InvalidArgumentException('EnvelopeBadDecodeCodec payload value must be a string.');
        }

        return new EnvelopeOtherAttribute($value);
    }
}
