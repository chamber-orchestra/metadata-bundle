<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Integrational\DependencyInjection;

use ChamberOrchestra\MetadataBundle\DependencyInjection\ChamberOrchestraMetadataExtension;
use ChamberOrchestra\MetadataBundle\EventSubscriber\MetadataSubscriber;
use ChamberOrchestra\MetadataBundle\Mapping\MetadataReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tests\Fixtures\Mapping\DummyMappingDriver;

final class ServicesConfigurationTest extends TestCase
{
    public function testServicesAreConfiguredAndAutoconfigured(): void
    {
        $container = new ContainerBuilder();
        $extension = new ChamberOrchestraMetadataExtension();
        $extension->load([], $container);

        self::assertTrue($container->hasDefinition(MetadataReader::class));
        self::assertTrue($container->getDefinition(MetadataReader::class)->isLazy());

        self::assertTrue($container->hasDefinition(MetadataSubscriber::class));

        $container->register('test.mapping_driver', DummyMappingDriver::class)
            ->setAutoconfigured(true)
            ->setPublic(true);

        $container->compile();

        $definition = $container->getDefinition('test.mapping_driver');
        self::assertArrayHasKey('chamber_orchestra_metadata.mapping.driver', $definition->getTags());
    }
}
