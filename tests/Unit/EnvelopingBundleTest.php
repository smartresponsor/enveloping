<?php

declare(strict_types=1);

namespace App\Enveloping\Tests\Unit;

use App\Enveloping\Codec\EnvelopeAttributeCodec;
use App\Enveloping\Codec\EnvelopeCodec;
use App\Enveloping\Codec\EnvelopeJsonCodec;
use App\Enveloping\Codec\EnvelopeMessengerCodec;
use App\Enveloping\DependencyInjection\EnvelopingExtension;
use App\Enveloping\EnvelopingBundle;
use App\Enveloping\Factory\EnvelopeFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EnvelopingBundleTest extends TestCase
{
    public function testBundleExposesItsPackageExtension(): void
    {
        $extension = (new EnvelopingBundle())->getContainerExtension();

        self::assertInstanceOf(EnvelopingExtension::class, $extension);
        self::assertSame('enveloping', $extension->getAlias());
    }

    public function testAttributeCodecsAreAutoconfiguredIntoTheRegistryTag(): void
    {
        $container = new ContainerBuilder();

        (new EnvelopingExtension())->load([], $container);

        $autoconfigured = $container->getAutoconfiguredInstanceof();

        self::assertArrayHasKey(EnvelopeAttributeCodec::class, $autoconfigured);
        self::assertArrayHasKey(
            'enveloping.attribute_codec',
            $autoconfigured[EnvelopeAttributeCodec::class]->getTags(),
        );
    }

    public function testMessengerRemainsOptionalInDevelopmentAndProductionManifests(): void
    {
        foreach (['composer.json', 'composer.prod.json'] as $manifestName) {
            $json = file_get_contents(\dirname(__DIR__, 2).'/'.$manifestName);
            self::assertIsString($json);

            $manifest = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);

            self::assertIsArray($manifest);

            $require = $manifest['require'] ?? null;
            $requireDev = $manifest['require-dev'] ?? null;
            $suggest = $manifest['suggest'] ?? null;

            self::assertIsArray($require);
            self::assertIsArray($requireDev);
            self::assertIsArray($suggest);
            self::assertArrayNotHasKey('symfony/messenger', $require);
            self::assertSame('^8.1', $requireDev['symfony/messenger'] ?? null);
            self::assertArrayHasKey('symfony/messenger', $suggest);
        }
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
        self::assertTrue($container->hasDefinition(EnvelopeCodec::class));
        self::assertTrue($container->hasDefinition(EnvelopeJsonCodec::class));

        if (interface_exists(\Symfony\Component\Messenger\Stamp\StampInterface::class)) {
            self::assertTrue($container->hasDefinition(EnvelopeMessengerCodec::class));
        } else {
            self::assertFalse($container->hasDefinition(EnvelopeMessengerCodec::class));
        }
    }
}
