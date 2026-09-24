<?php

declare(strict_types=1);

namespace App\Enveloping\Tests\Unit;

use App\Enveloping\Attribute\ActorAttribute;
use App\Enveloping\Attribute\CorrelationAttribute;
use App\Enveloping\Attribute\OriginAttribute;
use App\Enveloping\Envelope\Envelope;
use PHPUnit\Framework\TestCase;

final class EnvelopeTest extends TestCase
{
    public function testSubjectDoesNotNeedToKnowAboutEnveloping(): void
    {
        $subject = new \stdClass();
        $subject->id = 'subject-1';

        $envelope = new Envelope($subject, [
            new ActorAttribute('actor-1'),
            new OriginAttribute('checkout'),
        ]);

        self::assertSame($subject, $envelope->subject);
        self::assertSame('actor-1', $envelope->last(ActorAttribute::class)?->identity);
        self::assertSame('checkout', $envelope->last(OriginAttribute::class)?->source);
    }

    public function testAttributesAreImmutableAndCanRepeatByType(): void
    {
        $original = new Envelope('subject', [new CorrelationAttribute('first')]);
        $extended = $original->with(new CorrelationAttribute('second'));

        self::assertSame('first', $original->last(CorrelationAttribute::class)?->id);
        self::assertSame('second', $extended->last(CorrelationAttribute::class)?->id);
        self::assertCount(2, $extended->all(CorrelationAttribute::class));
    }

    public function testAttributeTypeCanBeRemovedWithoutChangingSubject(): void
    {
        $subject = new \stdClass();
        $envelope = new Envelope($subject, [
            new ActorAttribute('actor-1'),
            new OriginAttribute('api'),
        ]);

        $withoutActor = $envelope->without(ActorAttribute::class);

        self::assertSame($subject, $withoutActor->subject);
        self::assertNull($withoutActor->last(ActorAttribute::class));
        self::assertSame('api', $withoutActor->last(OriginAttribute::class)?->source);
    }
}
