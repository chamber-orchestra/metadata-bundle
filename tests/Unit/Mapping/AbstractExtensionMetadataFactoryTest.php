<?php

declare(strict_types=1);

namespace Tests\Unit\Mapping;

use ChamberOrchestra\MetadataBundle\Mapping\AbstractExtensionMetadataFactory;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use ChamberOrchestra\MetadataBundle\Mapping\ORM\ExtensionMetadata;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata as OrmClassMetadata;
use Doctrine\ORM\Mapping\EmbeddedClassMapping;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tests\Fixtures\Entity\EmbeddedValue;
use Tests\Fixtures\Entity\EntityWithEmbedded;

final class AbstractExtensionMetadataFactoryTest extends TestCase
{
    public function testCachesLoadedMetadataInMemory(): void
    {
        $factory = new TestExtensionMetadataFactory();

        $configuration = new Configuration();

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getConfiguration')->willReturn($configuration);

        $metadata = new OrmClassMetadata(EntityWithEmbedded::class);

        $first = $factory->getMetadataFor($entityManager, $metadata);
        $second = $factory->getMetadataFor($entityManager, $metadata);

        self::assertSame($first, $second);
        self::assertSame(1, $factory->loadCalls);
    }

    public function testUsesMetadataCacheAndLoadsEmbeddedMetadata(): void
    {
        $cache = new ArrayAdapter();
        $configuration = new Configuration();
        $configuration->setMetadataCache($cache);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConfiguration')->willReturn($configuration);

        $metadata = new OrmClassMetadata(EntityWithEmbedded::class);
        $metadata->embeddedClasses = [
            'embedded' => new EmbeddedClassMapping(EmbeddedValue::class),
        ];

        $embeddedMetadata = new OrmClassMetadata(EmbeddedValue::class);

        $entityManager
            ->expects(self::exactly(2))
            ->method('getClassMetadata')
            ->with(EmbeddedValue::class)
            ->willReturn($embeddedMetadata);

        $factory = new TestExtensionMetadataFactory();

        $first = $factory->getMetadataFor($entityManager, $metadata);

        self::assertSame(2, $factory->loadCalls);
        self::assertSame(0, $factory->wakeupCalls);
        self::assertArrayHasKey('embedded', $first->getEmbeddedMetadata());

        $secondFactory = new TestExtensionMetadataFactory();

        $second = $secondFactory->getMetadataFor($entityManager, $metadata);

        self::assertSame(0, $secondFactory->loadCalls);
        self::assertSame(2, $secondFactory->wakeupCalls);
        self::assertArrayHasKey('embedded', $second->getEmbeddedMetadata());

        // Verify metadata is functionally usable after cache restoration
        self::assertSame(EntityWithEmbedded::class, $second->getName());
        self::assertSame(EntityWithEmbedded::class, $second->getOriginMetadata()->getName());
    }
}

final class TestExtensionMetadataFactory extends AbstractExtensionMetadataFactory
{
    public int $loadCalls = 0;
    public int $wakeupCalls = 0;

    protected function doLoadMetadata(ExtensionMetadataInterface $class): void
    {
        ++$this->loadCalls;
    }

    protected function newClassMetadataInstance(ClassMetadata $metadata): ExtensionMetadataInterface
    {
        return new ExtensionMetadata($metadata);
    }

    protected function getCacheNamespace(): string
    {
        return 'TestFactory';
    }

    protected function wakeup(EntityManagerInterface $em, ClassMetadata $classMetadata, ExtensionMetadataInterface $extensionMetadata): void
    {
        ++$this->wakeupCalls;

        parent::wakeup($em, $classMetadata, $extensionMetadata);
    }
}
