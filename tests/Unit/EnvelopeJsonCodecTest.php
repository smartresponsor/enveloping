<?php

declare(strict_types=1);

namespace App\Enveloping\Tests\Unit;

use App\Enveloping\Codec\EnvelopeBuiltInAttributeCodec;
use App\Enveloping\Codec\EnvelopeCodec;
use App\Enveloping\Codec\EnvelopeJsonCodec;
use App\Enveloping\Exception\EnvelopeTransportException;
use App\Enveloping\Registry\EnvelopeAttributeCodecRegistry;
use App\Enveloping\ValueObject\Envelope;
use App\Enveloping\ValueObject\EnvelopeActorAttribute;
use App\Enveloping\ValueObject\EnvelopeCorrelationAttribute;
use PHPUnit\Framework\TestCase;

final class EnvelopeJsonCodecTest extends TestCase
{
    private EnvelopeJsonCodec $codec;

    protected function setUp(): void
    {
        $this->codec = new EnvelopeJsonCodec(new EnvelopeCodec(
            new EnvelopeAttributeCodecRegistry([
                new EnvelopeBuiltInAttributeCodec(),
            ]),
        ));
    }

    public function testEnvelopeRoundTripsThroughJsonWithExternalSubjectCodec(): void
    {
        $subject = new EnvelopeJsonTestSubject('subject-1');
        $envelope = new Envelope($subject, [
            new EnvelopeActorAttribute('actor-1'),
            new EnvelopeCorrelationAttribute('corr-1'),
        ]);

        $json = $this->codec->encode(
            $envelope,
            static function (mixed $value): array {
                if (!$value instanceof EnvelopeJsonTestSubject) {
                    throw new \InvalidArgumentException('Expected EnvelopeJsonTestSubject.');
                }

                return ['id' => $value->id];
            },
        );

        self::assertSame(
            '{"version":1,"subject":{"id":"subject-1"},"attributes":[{"type":"actor","payload":{"identity":"actor-1"}},{"type":"correlation","payload":{"id":"corr-1"}}]}',
            $json,
        );

        $decoded = $this->codec->decode(
            $json,
            static function (mixed $value): EnvelopeJsonTestSubject {
                if (!\is_array($value) || !\is_string($value['id'] ?? null)) {
                    throw new \InvalidArgumentException('Expected encoded subject map.');
                }

                return new EnvelopeJsonTestSubject($value['id']);
            },
        );

        self::assertInstanceOf(EnvelopeJsonTestSubject::class, $decoded->subject);
        self::assertSame('subject-1', $decoded->subject->id);
        self::assertSame('actor-1', $decoded->last(EnvelopeActorAttribute::class)?->identity);
        self::assertSame('corr-1', $decoded->last(EnvelopeCorrelationAttribute::class)?->id);
    }

    public function testJsonCodecRejectsSubjectEncoderThatLeavesAnObject(): void
    {
        $this->expectException(EnvelopeTransportException::class);
        $this->expectExceptionMessage('JSON-safe');

        $this->codec->encode(
            new Envelope(new EnvelopeJsonTestSubject('subject-1')),
            static fn (mixed $value): mixed => $value,
        );
    }

    public function testJsonCodecWrapsNativeJsonFailuresAsTransportExceptions(): void
    {
        try {
            $this->codec->decode('{', static fn (mixed $value): mixed => $value);
            self::fail('Invalid JSON must fail.');
        } catch (EnvelopeTransportException $exception) {
            self::assertInstanceOf(\JsonException::class, $exception->getPrevious());
        }

        try {
            $this->codec->encode(
                new Envelope(\NAN),
                static fn (mixed $value): mixed => $value,
            );
            self::fail('Non-encodable JSON value must fail.');
        } catch (EnvelopeTransportException $exception) {
            self::assertInstanceOf(\JsonException::class, $exception->getPrevious());
        }
    }

    public function testJsonCodecRejectsMalformedEnvelopeDocumentShapes(): void
    {
        $documents = [
            '[]',
            '{"subject":{},"attributes":[]}',
            '{"version":"1","subject":{},"attributes":[]}',
            '{"version":1,"attributes":[]}',
            '{"version":1,"subject":{},"attributes":{}}',
            '{"version":1,"subject":{},"attributes":[[]]}',
            '{"version":1,"subject":{},"attributes":[{"type":"","payload":{}}]}',
            '{"version":1,"subject":{},"attributes":[{"type":"actor","payload":"invalid"}]}',
            '{"version":1,"subject":{},"attributes":[],"extra":true}',
            '{"version":1,"subject":{},"attributes":[{"type":"actor","payload":{"identity":"actor-1"},"extra":true}]}',
        ];

        foreach ($documents as $json) {
            try {
                $this->codec->decode($json, static fn (mixed $value): mixed => $value);
                self::fail('Malformed Envelope JSON document should be rejected.');
            } catch (EnvelopeTransportException) {
                self::addToAssertionCount(1);
            }
        }
    }

    public function testJsonCodecRejectsUnsupportedTransportVersion(): void
    {
        $this->expectException(EnvelopeTransportException::class);
        $this->expectExceptionMessage('Unsupported envelope transport version 2');

        $this->codec->decode(
            '{"version":2,"subject":"subject","attributes":[]}',
            static fn (mixed $value): mixed => $value,
        );
    }

    public function testJsonCodecPreservesNestedJsonSafeSubjectPayload(): void
    {
        $json = '{"version":1,"subject":{"nested":{"list":[1,2,3],"flag":true}},"attributes":[]}';

        $decoded = $this->codec->decode(
            $json,
            static fn (mixed $value): mixed => $value,
        );

        self::assertSame([
            'nested' => [
                'list' => [1, 2, 3],
                'flag' => true,
            ],
        ], $decoded->subject);
    }
}

final readonly class EnvelopeJsonTestSubject
{
    public function __construct(public string $id)
    {
    }
}
