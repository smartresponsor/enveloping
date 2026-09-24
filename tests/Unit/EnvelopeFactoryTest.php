<?php

declare(strict_types=1);

namespace App\Enveloping\Tests\Unit;

use App\Enveloping\Factory\EnvelopeFactory;
use App\Enveloping\ValueObject\EnvelopeCorrelationAttribute;
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
}
