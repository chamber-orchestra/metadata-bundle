<?php

declare(strict_types=1);

namespace Tests\Unit\EventSubscriber;

use ChamberOrchestra\MetadataBundle\EventSubscriber\MetadataSubscriber;
use ChamberOrchestra\MetadataBundle\Mapping\MetadataReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

final class MetadataSubscriberTest extends TestCase
{
    public function testLoadClassMetadataDelegatesToReader(): void
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $classMetadata = new ClassMetadata(\stdClass::class);

        $eventArgs = $this->createStub(LoadClassMetadataEventArgs::class);
        $eventArgs->method('getEntityManager')->willReturn($entityManager);
        $eventArgs->method('getClassMetadata')->willReturn($classMetadata);

        $reader = $this->createMock(MetadataReader::class);
        $reader
            ->expects(self::once())
            ->method('loadExtensionMetadata')
            ->with($entityManager, $classMetadata);

        $subscriber = new MetadataSubscriber($reader);
        $subscriber->loadClassMetadata($eventArgs);
    }
}
