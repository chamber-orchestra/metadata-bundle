<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Helper;

use ChamberOrchestra\MetadataBundle\Helper\MetadataArgs;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
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
        $configuration = new NamedMetadataConfiguration('App\\Entity\\CustomName');
        $entity = new SimpleEntity();
        $classMetadata = new ClassMetadata('App\\Entity\\CustomName');

        $entityManager
            ->expects(self::once())
            ->method('getClassMetadata')
            ->with('App\\Entity\\CustomName')
            ->willReturn($classMetadata);

        $args = new MetadataArgs($entityManager, $extensionMetadata, $configuration, $entity);

        self::assertSame($classMetadata, $args->getClassMetadata());
        self::assertSame($classMetadata, $args->getClassMetadata());
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

        self::assertSame($classMetadata, $args->getClassMetadata());
        self::assertSame($classMetadata, $args->getClassMetadata());
    }
}
