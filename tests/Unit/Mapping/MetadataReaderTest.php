<?php

declare(strict_types=1);

namespace Tests\Unit\Mapping;

use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use ChamberOrchestra\MetadataBundle\Mapping\MetadataReader;
use ChamberOrchestra\MetadataBundle\Mapping\ORM\ExtensionMetadataFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Entity\SimpleEntity;
use Tests\Fixtures\Mapping\TestMetadataConfiguration;

final class MetadataReaderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $property = new \ReflectionProperty(MetadataReader::class, 'configurations');
        $property->setValue(null, []);
    }

    public function testLoadsAndCachesMetadata(): void
    {
        $metadata = new ClassMetadata(SimpleEntity::class);
        $extensionMetadata = $this->createStub(ExtensionMetadataInterface::class);

        $factory = $this->createMock(ExtensionMetadataFactory::class);
        $factory
            ->expects(self::once())
            ->method('getMetadataFor')
            ->with(self::isInstanceOf(EntityManagerInterface::class), $metadata)
            ->willReturn($extensionMetadata);

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getClassMetadata')->with(SimpleEntity::class)->willReturn($metadata);

        $reader = new MetadataReader($factory);

        $first = $reader->getExtensionMetadata($entityManager, SimpleEntity::class);
        $second = $reader->getExtensionMetadata($entityManager, SimpleEntity::class);

        self::assertSame($extensionMetadata, $first);
        self::assertSame($extensionMetadata, $second);
    }

    public function testGetsExtensionConfiguration(): void
    {
        $metadata = new ClassMetadata(SimpleEntity::class);
        $configuration = new TestMetadataConfiguration();

        $extensionMetadata = $this->createMock(ExtensionMetadataInterface::class);
        $extensionMetadata
            ->expects(self::once())
            ->method('getConfiguration')
            ->with(TestMetadataConfiguration::class)
            ->willReturn($configuration);

        $factory = $this->createMock(ExtensionMetadataFactory::class);
        $factory
            ->expects(self::once())
            ->method('getMetadataFor')
            ->willReturn($extensionMetadata);

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getClassMetadata')->with(SimpleEntity::class)->willReturn($metadata);

        $reader = new MetadataReader($factory);

        $result = $reader->getExtensionConfiguration($entityManager, SimpleEntity::class, TestMetadataConfiguration::class);

        self::assertSame($configuration, $result);
    }
}
