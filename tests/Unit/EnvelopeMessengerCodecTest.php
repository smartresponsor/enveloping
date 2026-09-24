<?php

declare(strict_types=1);

namespace App\Enveloping\Tests\Unit;

use App\Enveloping\Codec\EnvelopeBuiltInAttributeCodec;
use App\Enveloping\Codec\EnvelopeMessengerCodec;
use App\Enveloping\DTO\EnvelopeTransportDTO;
use App\Enveloping\Message\EnvelopeContextStamp;
use App\Enveloping\Registry\EnvelopeAttributeCodecRegistry;
use App\Enveloping\ValueObject\Envelope;
use App\Enveloping\ValueObject\EnvelopeActorAttribute;
use App\Enveloping\ValueObject\EnvelopeCorrelationAttribute;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope as MessengerEnvelope;

final class EnvelopeMessengerCodecTest extends TestCase
{
    private EnvelopeMessengerCodec $codec;

    protected function setUp(): void
    {
        $this->codec = new EnvelopeMessengerCodec(new EnvelopeAttributeCodecRegistry([
            new EnvelopeBuiltInAttributeCodec(),
        ]));
    }

    public function testContextRoundTripsThroughMessengerStampWithoutChangingMessage(): void
    {
        $message = new EnvelopeMessengerTestMessage('message-1');
        $context = new Envelope($message, [
            new EnvelopeActorAttribute('actor-1'),
            new EnvelopeCorrelationAttribute('corr-1'),
        ]);

        $messenger = $this->codec->toMessenger($context);

        self::assertSame($message, $messenger->getMessage());

        $stamp = $messenger->last(EnvelopeContextStamp::class);
        self::assertInstanceOf(EnvelopeContextStamp::class, $stamp);
        self::assertSame(EnvelopeTransportDTO::CURRENT_VERSION, $stamp->version);
        self::assertSame(['actor', 'correlation'], array_column($stamp->attributes, 'type'));

        $decoded = $this->codec->fromMessenger($messenger);

        self::assertSame($message, $decoded->subject);
        self::assertSame('actor-1', $decoded->last(EnvelopeActorAttribute::class)?->identity);
        self::assertSame('corr-1', $decoded->last(EnvelopeCorrelationAttribute::class)?->id);
    }

    public function testMessengerEnvelopeWithoutContextStampProducesEmptyContext(): void
    {
        $message = new EnvelopeMessengerTestMessage('plain');

        $decoded = $this->codec->fromMessenger(new MessengerEnvelope($message));

        self::assertSame($message, $decoded->subject);
        self::assertSame([], $decoded->attributes());
    }

    public function testMessengerBridgeRejectsNonObjectSubjects(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('messages must be objects');

        $this->codec->toMessenger(new Envelope('not-an-object'));
    }

    public function testMessengerBridgeRejectsUnsupportedContextStampVersion(): void
    {
        $message = new EnvelopeMessengerTestMessage('message-1');
        $messenger = new MessengerEnvelope($message, [
            new EnvelopeContextStamp([], EnvelopeTransportDTO::CURRENT_VERSION + 1),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported envelope context stamp version');

        $this->codec->fromMessenger($messenger);
    }

    public function testContextStampIsSerializableAndReconstructsTransportAttributes(): void
    {
        $stamp = new EnvelopeContextStamp([
            ['type' => 'actor', 'payload' => ['identity' => 'actor-1']],
            ['type' => 'correlation', 'payload' => ['id' => 'corr-1']],
        ]);

        $copy = unserialize(serialize($stamp));

        self::assertInstanceOf(EnvelopeContextStamp::class, $copy);
        self::assertSame($stamp->attributes, $copy->attributes);
        self::assertSame(['actor', 'correlation'], array_map(
            static fn ($attribute): string => $attribute->type,
            $copy->transportAttributes(),
        ));
    }

    public function testContextStampRejectsNonPositiveVersion(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new EnvelopeContextStamp([], 0);
    }
}

final readonly class EnvelopeMessengerTestMessage
{
    public function __construct(public string $id)
    {
    }
}
