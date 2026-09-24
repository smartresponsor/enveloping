<?php

declare(strict_types=1);

namespace App\Enveloping\Tests\Unit;

use App\Enveloping\Codec\EnvelopeBuiltInAttributeCodec;
use App\Enveloping\Codec\EnvelopeCodec;
use App\Enveloping\Codec\EnvelopeJsonCodec;
use App\Enveloping\Codec\EnvelopeMessengerCodec;
use App\Enveloping\DTO\EnvelopeTransportDTO;
use App\Enveloping\Message\EnvelopeContextStamp;
use App\Enveloping\Registry\EnvelopeAttributeCodecRegistry;
use App\Enveloping\ValueObject\Envelope;
use App\Enveloping\ValueObject\EnvelopeActorAttribute;
use PHPUnit\Framework\TestCase;

final class EnvelopeTransportCompatibilityTest extends TestCase
{
    public function testCoreJsonAndMessengerAdaptersShareOneCurrentTransportVersion(): void
    {
        $registry = new EnvelopeAttributeCodecRegistry([
            new EnvelopeBuiltInAttributeCodec(),
        ]);
        $core = new EnvelopeCodec($registry);
        $json = new EnvelopeJsonCodec($core);
        $messenger = new EnvelopeMessengerCodec($registry);

        $message = new EnvelopeTransportCompatibilityMessage('message-1');
        $envelope = new Envelope($message, [
            new EnvelopeActorAttribute('actor-1'),
        ]);

        $transport = $core->encode(
            $envelope,
            static function (mixed $subject): array {
                if (!$subject instanceof EnvelopeTransportCompatibilityMessage) {
                    throw new \InvalidArgumentException('Expected EnvelopeTransportCompatibilityMessage.');
                }

                return ['id' => $subject->id];
            },
        );
        $jsonDocument = json_decode(
            $json->encode(
                $envelope,
                static function (mixed $subject): array {
                    if (!$subject instanceof EnvelopeTransportCompatibilityMessage) {
                        throw new \InvalidArgumentException('Expected EnvelopeTransportCompatibilityMessage.');
                    }

                    return ['id' => $subject->id];
                },
            ),
            true,
            512,
            \JSON_THROW_ON_ERROR,
        );
        $messengerEnvelope = $messenger->toMessenger($envelope);
        $stamp = $messengerEnvelope->last(EnvelopeContextStamp::class);

        self::assertSame(EnvelopeTransportDTO::CURRENT_VERSION, $transport->version);
        self::assertIsArray($jsonDocument);
        self::assertSame(EnvelopeTransportDTO::CURRENT_VERSION, $jsonDocument['version'] ?? null);
        self::assertInstanceOf(EnvelopeContextStamp::class, $stamp);
        self::assertSame(EnvelopeTransportDTO::CURRENT_VERSION, $stamp->version);
    }
}

final readonly class EnvelopeTransportCompatibilityMessage
{
    public function __construct(public string $id)
    {
    }
}
