<?php

declare(strict_types=1);

namespace App\Enveloping\Tests\Unit;

use App\Enveloping\DependencyInjection\EnvelopingExtension;
use App\Enveloping\EnvelopingBundle;
use App\Enveloping\Factory\EnvelopeFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EnvelopingBundleTest extends TestCase
{
    public function testBundleExposesItsPackageExtension(): void
    {
        self::assertInstanceOf(EnvelopingExtension::class, (new EnvelopingBundle())->getContainerExtension());
    }

    public function testExtensionLoadsPackageParametersAndFactoryService(): void
    {
        $container = new ContainerBuilder();

        (new EnvelopingExtension())->load([], $container);

        self::assertSame(
            \dirname(__DIR__, 2),
            $container->getParameter('enveloping.package_dir'),
        );
        self::assertTrue($container->hasDefinition(EnvelopeFactory::class));
    }
}
