<?php

declare(strict_types=1);

namespace App\Enveloping\Tests\Integration;

use App\Enveloping\Codec\EnvelopeBuiltInAttributeCodec;
use App\Enveloping\Codec\EnvelopeMessengerCodec;
use App\Enveloping\Factory\EnvelopeFactory;
use App\Enveloping\Registry\EnvelopeAttributeCodecRegistry;
use App\Enveloping\ValueObject\EnvelopeActorAttribute;
use App\Enveloping\ValueObject\EnvelopeCausationAttribute;
use App\Enveloping\ValueObject\EnvelopeCorrelationAttribute;
use App\Enveloping\ValueObject\EnvelopeOriginAttribute;
use PHPUnit\Framework\TestCase;

final class EnvelopePropagationMessengerIntegrationTest extends TestCase
{
    public function testSelectedParentContextCrossesMessengerBoundaryWithExplicitChildOverride(): void
    {
        $factory = new EnvelopeFactory();
        $messengerCodec = new EnvelopeMessengerCodec(
            new EnvelopeAttributeCodecRegistry([
                new EnvelopeBuiltInAttributeCodec(),
            ]),
        );

        $parent = $factory->create(
            new EnvelopeIntegrationRequest('request-1'),
            new EnvelopeOriginAttribute('webhook'),
            new EnvelopeActorAttribute('external-actor'),
            new EnvelopeCorrelationAttribute('corr-42'),
            new EnvelopeCausationAttribute('root-cause'),
        );

        $message = new EnvelopeIntegrationMessage('delivery-1');
        $child = $factory
            ->inheritOnly(
                $parent,
                $message,
                EnvelopeOriginAttribute::class,
                EnvelopeCorrelationAttribute::class,
            )
            ->replace(new EnvelopeActorAttribute('delivery-worker'));

        $transported = $messengerCodec->toMessenger($child);
        $received = $messengerCodec->fromMessenger($transported);

        self::assertSame($message, $transported->getMessage());
        self::assertSame($message, $received->subject);
        self::assertSame('webhook', $received->last(EnvelopeOriginAttribute::class)?->source);
        self::assertSame('corr-42', $received->last(EnvelopeCorrelationAttribute::class)?->id);
        self::assertSame('delivery-worker', $received->last(EnvelopeActorAttribute::class)?->identity);
        self::assertNull($received->last(EnvelopeCausationAttribute::class));

        self::assertSame('external-actor', $parent->last(EnvelopeActorAttribute::class)?->identity);
        self::assertSame('root-cause', $parent->last(EnvelopeCausationAttribute::class)?->id);
    }
}

final readonly class EnvelopeIntegrationRequest
{
    public function __construct(public string $id)
    {
    }
}

final readonly class EnvelopeIntegrationMessage
{
    public function __construct(public string $id)
    {
    }
}
