<?php

declare(strict_types=1);

namespace App\Enveloping\Tests\Unit;

use App\Enveloping\Factory\EnvelopeFactory;
use App\Enveloping\ValueObject\Envelope;
use App\Enveloping\ValueObject\EnvelopeActorAttribute;
use App\Enveloping\ValueObject\EnvelopeCausationAttribute;
use App\Enveloping\ValueObject\EnvelopeCorrelationAttribute;
use App\Enveloping\ValueObject\EnvelopeOriginAttribute;
use App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface;
use PHPUnit\Framework\TestCase;

final class EnvelopeFactoryTest extends TestCase
{
    public function testFactoryCreatesEnvelopeWithoutConsumerContract(): void
    {
        $subject = ['id' => 'operation-1'];
        $envelope = (new EnvelopeFactory())->create($subject, new EnvelopeCorrelationAttribute('corr-1'));

        self::assertSame($subject, $envelope->subject);
        self::assertSame('corr-1', $envelope->last(EnvelopeCorrelationAttribute::class)?->id);
    }

    public function testFactoryExplicitlyInheritsAllParentContext(): void
    {
        $actor = new EnvelopeActorAttribute('actor-1');
        $origin = new EnvelopeOriginAttribute('api');
        $correlation = new EnvelopeCorrelationAttribute('corr-1');
        $parent = new Envelope('parent', [$actor, $origin, $correlation]);

        $child = (new EnvelopeFactory())->inherit($parent, 'child');

        self::assertSame('child', $child->subject);
        self::assertSame([$actor, $origin, $correlation], $child->attributes());
        self::assertSame('parent', $parent->subject);
    }

    public function testFactoryExplicitlyInheritsOnlySelectedContextTypesInParentOrder(): void
    {
        $actor = new EnvelopeActorAttribute('actor-1');
        $origin = new EnvelopeOriginAttribute('api');
        $correlation = new EnvelopeCorrelationAttribute('corr-1');
        $causation = new EnvelopeCausationAttribute('cause-1');
        $parent = new Envelope('parent', [$origin, $actor, $correlation, $causation]);

        $child = (new EnvelopeFactory())->inheritOnly(
            $parent,
            'child',
            EnvelopeCorrelationAttribute::class,
            EnvelopeOriginAttribute::class,
        );

        self::assertSame([$origin, $correlation], $child->attributes());
    }

    public function testFactorySelectedInheritanceIsPolymorphic(): void
    {
        $parent = new Envelope('parent', [
            new EnvelopeActorAttribute('actor-1'),
            new EnvelopeOriginAttribute('api'),
        ]);

        $child = (new EnvelopeFactory())->inheritOnly(
            $parent,
            'child',
            EnvelopeAttributeInterface::class,
        );

        self::assertCount(2, $child->attributes());
    }

    public function testFactorySelectedInheritanceWithNoTypesMeansInheritNone(): void
    {
        $parent = new Envelope('parent', [
            new EnvelopeActorAttribute('actor-1'),
        ]);

        $child = (new EnvelopeFactory())->inheritOnly($parent, 'child');

        self::assertSame([], $child->attributes());
    }

    public function testFactorySelectedInheritanceRejectsInvalidAttributeClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must implement');

        // @phpstan-ignore-next-line deliberate invalid runtime input
        (new EnvelopeFactory())->inheritOnly(new Envelope('parent'), 'child', \stdClass::class);
    }

    public function testInheritedContextCanBeExplicitlyReplacedForChild(): void
    {
        $parent = new Envelope('parent', [
            new EnvelopeActorAttribute('parent-actor'),
            new EnvelopeCorrelationAttribute('corr-1'),
        ]);

        $child = (new EnvelopeFactory())
            ->inherit($parent, 'child')
            ->replace(new EnvelopeActorAttribute('child-actor'));

        self::assertSame('parent-actor', $parent->last(EnvelopeActorAttribute::class)?->identity);
        self::assertSame('child-actor', $child->last(EnvelopeActorAttribute::class)?->identity);
        self::assertSame('corr-1', $child->last(EnvelopeCorrelationAttribute::class)?->id);
    }
}
