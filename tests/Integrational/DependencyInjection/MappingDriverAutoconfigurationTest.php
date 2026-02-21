<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Integrational\DependencyInjection;

use ChamberOrchestra\MetadataBundle\Mapping\ORM\ExtensionMetadataFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Fixtures\Doctrine\Article;
use Tests\Fixtures\Mapping\TrackingMappingDriver;
use Tests\Integrational\MetadataTestKernel;

final class MappingDriverAutoconfigurationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return MetadataTestKernel::class;
    }

    public function testAutoconfiguredDriverIsInjectedIntoFactory(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $factory = $container->get(ExtensionMetadataFactory::class);
        $driver = $container->get(TrackingMappingDriver::class);

        $metadata = $entityManager->getClassMetadata(Article::class);
        $factory->getMetadataFor($entityManager, $metadata);

        self::assertGreaterThan(0, $driver->supportsCalls);
        self::assertGreaterThan(0, $driver->loadCalls);
    }
}
