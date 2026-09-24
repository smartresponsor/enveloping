<?php

declare(strict_types=1);

namespace App\Enveloping\Tests\Unit;

use App\Enveloping\Attribute\CorrelationAttribute;
use App\Enveloping\Factory\EnvelopeFactory;
use PHPUnit\Framework\TestCase;

final class EnvelopeFactoryTest extends TestCase
{
    public function testFactoryCreatesEnvelopeWithoutConsumerContract(): void
    {
        $subject = ['id' => 'operation-1'];
        $envelope = (new EnvelopeFactory())->create($subject, new CorrelationAttribute('corr-1'));

        self::assertSame($subject, $envelope->subject);
        self::assertSame('corr-1', $envelope->last(CorrelationAttribute::class)?->id);
    }
}
