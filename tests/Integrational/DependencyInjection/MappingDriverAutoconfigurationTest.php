<?php

declare(strict_types=1);

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
        TrackingMappingDriver::reset();

        self::bootKernel();

        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $factory = $container->get(ExtensionMetadataFactory::class);

        $metadata = $entityManager->getClassMetadata(Article::class);
        $factory->getMetadataFor($entityManager, $metadata);

        self::assertGreaterThan(0, TrackingMappingDriver::$supportsCalls);
        self::assertGreaterThan(0, TrackingMappingDriver::$loadCalls);
    }
}
