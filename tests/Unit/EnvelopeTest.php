<?php

declare(strict_types=1);

namespace App\Enveloping\Tests\Unit;

use App\Enveloping\ValueObject\Envelope;
use App\Enveloping\ValueObject\EnvelopeActorAttribute;
use App\Enveloping\ValueObject\EnvelopeCorrelationAttribute;
use App\Enveloping\ValueObject\EnvelopeOriginAttribute;
use PHPUnit\Framework\TestCase;

final class EnvelopeTest extends TestCase
{
    public function testSubjectDoesNotNeedToKnowAboutEnveloping(): void
    {
        $subject = new \stdClass();
        $subject->id = 'subject-1';

        $envelope = new Envelope($subject, [
            new EnvelopeActorAttribute('actor-1'),
            new EnvelopeOriginAttribute('checkout'),
        ]);

        self::assertSame($subject, $envelope->subject);
        self::assertSame('actor-1', $envelope->last(EnvelopeActorAttribute::class)?->identity);
        self::assertSame('checkout', $envelope->last(EnvelopeOriginAttribute::class)?->source);
    }

    public function testAttributesAreImmutableAndCanRepeatByType(): void
    {
        $original = new Envelope('subject', [new EnvelopeCorrelationAttribute('first')]);
        $extended = $original->with(new EnvelopeCorrelationAttribute('second'));

        self::assertSame('first', $original->last(EnvelopeCorrelationAttribute::class)?->id);
        self::assertSame('second', $extended->last(EnvelopeCorrelationAttribute::class)?->id);
        self::assertCount(2, $extended->all(EnvelopeCorrelationAttribute::class));
    }

    public function testEmptyAndIntrospectionOperationsAreDeterministic(): void
    {
        $envelope = new Envelope('subject');

        self::assertSame($envelope, $envelope->with());
        self::assertNull($envelope->last(EnvelopeCorrelationAttribute::class));
        self::assertSame([], $envelope->all(EnvelopeCorrelationAttribute::class));
        self::assertSame([], $envelope->attributes());

        $withCause = $envelope->with(new \App\Enveloping\ValueObject\EnvelopeCausationAttribute('cause-1'));
        self::assertSame('cause-1', $withCause->last(\App\Enveloping\ValueObject\EnvelopeCausationAttribute::class)?->id);
        self::assertSame($withCause->attributes(), $withCause->without(EnvelopeActorAttribute::class)->attributes());
    }

    public function testPolymorphicAttributeLookupSeesAllEnvelopeAttributes(): void
    {
        $actor = new EnvelopeActorAttribute('actor-1');
        $origin = new EnvelopeOriginAttribute('api');
        $envelope = new Envelope('subject', [$actor, $origin]);

        self::assertTrue($envelope->has(\App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface::class));
        self::assertSame([$actor, $origin], $envelope->all(\App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface::class));
        self::assertSame($origin, $envelope->last(\App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface::class));
    }

    public function testReplaceMakesCardinalityAnExplicitCallerChoice(): void
    {
        $envelope = new Envelope('subject', [
            new EnvelopeCorrelationAttribute('first'),
            new EnvelopeCorrelationAttribute('second'),
            new EnvelopeOriginAttribute('api'),
        ]);

        $replaced = $envelope->replace(new EnvelopeCorrelationAttribute('replacement'));

        self::assertCount(2, $envelope->all(EnvelopeCorrelationAttribute::class));
        self::assertSame(['replacement'], array_map(
            static fn (EnvelopeCorrelationAttribute $attribute): string => $attribute->id,
            $replaced->all(EnvelopeCorrelationAttribute::class),
        ));
        self::assertSame('api', $replaced->last(EnvelopeOriginAttribute::class)?->source);
    }

    public function testSubjectCanBeReboundWithoutChangingContext(): void
    {
        $actor = new EnvelopeActorAttribute('actor-1');
        $envelope = new Envelope('first-subject', [$actor]);

        $rebound = $envelope->withSubject('second-subject');

        self::assertSame('first-subject', $envelope->subject);
        self::assertSame('second-subject', $rebound->subject);
        self::assertSame([$actor], $rebound->attributes());
        self::assertSame($envelope, $envelope->withSubject('first-subject'));
    }

    public function testPolymorphicRemovalCanClearTheWholeContextExplicitly(): void
    {
        $envelope = new Envelope('subject', [
            new EnvelopeActorAttribute('actor-1'),
            new EnvelopeOriginAttribute('api'),
        ]);

        $cleared = $envelope->without(\App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface::class);

        self::assertSame([], $cleared->attributes());
        self::assertSame($envelope, $envelope->without(EnvelopeCorrelationAttribute::class));
    }

    public function testAttributeTypeCanBeRemovedWithoutChangingSubject(): void
    {
        $subject = new \stdClass();
        $envelope = new Envelope($subject, [
            new EnvelopeActorAttribute('actor-1'),
            new EnvelopeOriginAttribute('api'),
        ]);

        $withoutActor = $envelope->without(EnvelopeActorAttribute::class);

        self::assertSame($subject, $withoutActor->subject);
        self::assertNull($withoutActor->last(EnvelopeActorAttribute::class));
        self::assertSame('api', $withoutActor->last(EnvelopeOriginAttribute::class)?->source);
    }
}
