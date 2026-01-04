<?php

declare(strict_types=1);

namespace Tests\Integrational;

use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use ChamberOrchestra\MetadataBundle\Mapping\MetadataReader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Fixtures\Doctrine\Article;

final class MetadataReaderIntegrationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return MetadataTestKernel::class;
    }

    public function testMetadataReaderCachesExtensionMetadataForEntity(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $reader = $container->get(MetadataReader::class);

        $metadata = $entityManager->getClassMetadata(Article::class);

        $reader->loadExtensionMetadata($entityManager, $metadata);
        $first = $reader->getExtensionMetadata($entityManager, Article::class);
        $second = $reader->getExtensionMetadata($entityManager, Article::class);

        self::assertInstanceOf(ExtensionMetadataInterface::class, $first);
        self::assertSame($first, $second);
        self::assertSame(Article::class, $first->getName());
    }
}
