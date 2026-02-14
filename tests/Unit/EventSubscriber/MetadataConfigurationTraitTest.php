<?php

declare(strict_types=1);

namespace Tests\Unit\EventSubscriber;

use ChamberOrchestra\MetadataBundle\EventSubscriber\MetadataConfigurationTrait;
use ChamberOrchestra\MetadataBundle\Exception\LogicException;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use ChamberOrchestra\MetadataBundle\Mapping\MetadataReader;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Entity\SimpleEntity;
use Tests\Fixtures\Mapping\TestMetadataConfiguration;

final class MetadataConfigurationTraitTest extends TestCase
{
    public function testGetConfigurationUsesReader(): void
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $configuration = new TestMetadataConfiguration();
        $metadata = $this->createMock(ExtensionMetadataInterface::class);
        $metadata
            ->expects(self::once())
            ->method('getConfiguration')
            ->with(TestMetadataConfiguration::class)
            ->willReturn($configuration);

        $reader = $this->createMock(MetadataReader::class);
        $reader
            ->expects(self::once())
            ->method('getExtensionMetadata')
            ->with($entityManager, SimpleEntity::class)
            ->willReturn($metadata);

        $subject = new class() {
            use MetadataConfigurationTrait;

            public function getConfig(EntityManagerInterface $em, object $entity, string $class): ?TestMetadataConfiguration
            {
                return $this->getConfiguration($em, $entity, $class);
            }
        };

        $subject->setMetadataReader($reader);

        $result = $subject->getConfig($entityManager, new SimpleEntity(), TestMetadataConfiguration::class);

        self::assertSame($configuration, $result);
    }

    public function testThrowsWhenReaderNotInjected(): void
    {
        $subject = new class() {
            use MetadataConfigurationTrait;

            public function getConfig(EntityManagerInterface $em, object $entity, string $class): mixed
            {
                return $this->getConfiguration($em, $entity, $class);
            }
        };

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/MetadataReader/');

        $subject->getConfig(
            $this->createStub(EntityManagerInterface::class),
            new SimpleEntity(),
            TestMetadataConfiguration::class
        );
    }
}
