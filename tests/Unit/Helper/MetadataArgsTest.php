<?php

declare(strict_types=1);

namespace Tests\Unit\Helper;

use ChamberOrchestra\MetadataBundle\Helper\MetadataArgs;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Entity\SimpleEntity;
use Tests\Fixtures\Mapping\NamedMetadataConfiguration;
use Tests\Fixtures\Mapping\TestMetadataConfiguration;

final class MetadataArgsTest extends TestCase
{
    public function testResolvesClassMetadataFromConfigurationEntityName(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $extensionMetadata = $this->createStub(ExtensionMetadataInterface::class);
        $configuration = new NamedMetadataConfiguration(SimpleEntity::class);
        $entity = new SimpleEntity();
        $classMetadata = new ClassMetadata(SimpleEntity::class);

        $entityManager
            ->expects(self::once())
            ->method('getClassMetadata')
            ->with(SimpleEntity::class)
            ->willReturn($classMetadata);

        $args = new MetadataArgs($entityManager, $extensionMetadata, $configuration, $entity);

        self::assertSame($classMetadata, $this->readClassMetadata($args));
        self::assertSame($classMetadata, $this->readClassMetadata($args));
    }

    public function testResolvesClassMetadataFromEntityInstance(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $extensionMetadata = $this->createStub(ExtensionMetadataInterface::class);
        $configuration = new TestMetadataConfiguration();
        $entity = new SimpleEntity();
        $classMetadata = new ClassMetadata(SimpleEntity::class);

        $entityManager
            ->expects(self::once())
            ->method('getClassMetadata')
            ->with(SimpleEntity::class)
            ->willReturn($classMetadata);

        $args = new MetadataArgs($entityManager, $extensionMetadata, $configuration, $entity);

        self::assertSame($classMetadata, $this->readClassMetadata($args));
        self::assertSame($classMetadata, $this->readClassMetadata($args));
    }

    private function readClassMetadata(MetadataArgs $args): ClassMetadata
    {
        $reader = function (MetadataArgs $args): ClassMetadata {
            return $args->classMetadata;
        };

        $getter = $reader->bindTo($args, MetadataArgs::class);

        return $getter($args);
    }
}
