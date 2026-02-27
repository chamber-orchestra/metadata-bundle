<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\EventSubscriber;

use ChamberOrchestra\MetadataBundle\EventSubscriber\AbstractDoctrineListener;
use ChamberOrchestra\MetadataBundle\Helper\MetadataArgs;
use ChamberOrchestra\MetadataBundle\Mapping\MetadataReader;
use ChamberOrchestra\MetadataBundle\Mapping\ORM\ExtensionMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Entity\SimpleEntity;
use Tests\Fixtures\Mapping\TestMetadataConfiguration;

final class AbstractDoctrineListenerTest extends TestCase
{
    public function testFiltersScheduledEntitiesByConfiguration(): void
    {
        $configuredEntity = new SimpleEntity();
        $unconfiguredEntity = new class {};

        $configuration = new TestMetadataConfiguration();
        $configuredMetadata = new ExtensionMetadata(new ClassMetadata(SimpleEntity::class));
        $configuredMetadata->addConfiguration($configuration);

        $unconfiguredMetadata = new ExtensionMetadata(new ClassMetadata($unconfiguredEntity::class));

        $metadataByClass = [
            SimpleEntity::class => $configuredMetadata,
            $unconfiguredEntity::class => $unconfiguredMetadata,
        ];

        $reader = $this->createStub(MetadataReader::class);
        $reader
            ->method('getExtensionMetadata')
            ->willReturnCallback(static fn (EntityManagerInterface $em, string $class): ExtensionMetadata => $metadataByClass[$class]);

        $unitOfWork = $this->createStub(UnitOfWork::class);
        $unitOfWork->method('getScheduledEntityInsertions')->willReturn([$configuredEntity, $unconfiguredEntity]);
        $unitOfWork->method('getScheduledEntityUpdates')->willReturn([$unconfiguredEntity, $configuredEntity]);
        $unitOfWork->method('getScheduledEntityDeletions')->willReturn([$unconfiguredEntity]);

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getUnitOfWork')->willReturn($unitOfWork);

        $listener = new class extends AbstractDoctrineListener {
            public function insertions(EntityManagerInterface $em, string $class): array
            {
                return $this->getScheduledEntityInsertions($em, $class);
            }

            public function updates(EntityManagerInterface $em, string $class): array
            {
                return $this->getScheduledEntityUpdates($em, $class);
            }

            public function deletions(EntityManagerInterface $em, string $class): array
            {
                return $this->getScheduledEntityDeletions($em, $class);
            }
        };

        $listener->setMetadataReader($reader);

        $insertions = \array_values($listener->insertions($entityManager, TestMetadataConfiguration::class));
        $updates = \array_values($listener->updates($entityManager, TestMetadataConfiguration::class));
        $deletions = \array_values($listener->deletions($entityManager, TestMetadataConfiguration::class));

        self::assertCount(1, $insertions);
        self::assertSame($configuredEntity, $insertions[0]->entity);
        self::assertSame($configuration, $insertions[0]->configuration);

        self::assertCount(1, $updates);
        self::assertSame($configuredEntity, $updates[0]->entity);

        self::assertCount(0, $deletions);
    }

    public function testGetEntityChangeSetDelegatesToUnitOfWork(): void
    {
        $entity = new SimpleEntity();
        $changeSet = ['name' => ['old', 'new']];

        $unitOfWork = $this->createStub(UnitOfWork::class);
        $unitOfWork->method('getEntityChangeSet')->willReturn($changeSet);

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getUnitOfWork')->willReturn($unitOfWork);

        $extensionMetadata = new ExtensionMetadata(new ClassMetadata(SimpleEntity::class));
        $configuration = new TestMetadataConfiguration();

        $args = new MetadataArgs($entityManager, $extensionMetadata, $configuration, $entity);

        $listener = new class extends AbstractDoctrineListener {
            public function changeSet(EntityManagerInterface $em, MetadataArgs $args): array
            {
                return $this->getEntityChangeSet($em, $args);
            }
        };

        self::assertSame($changeSet, $listener->changeSet($entityManager, $args));
    }
}
