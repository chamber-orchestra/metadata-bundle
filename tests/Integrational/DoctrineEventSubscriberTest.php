<?php

declare(strict_types=1);

namespace Tests\Integrational;

use ChamberOrchestra\MetadataBundle\EventSubscriber\MetadataSubscriber;
use Doctrine\ORM\Events;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineEventSubscriberTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return MetadataTestKernel::class;
    }

    public function testMetadataSubscriberIsRegisteredInDoctrineEventManager(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $listeners = $entityManager->getEventManager()->getListeners(Events::loadClassMetadata);

        self::assertNotEmpty($listeners);
        self::assertTrue($this->containsMetadataSubscriber($listeners));
    }

    private function containsMetadataSubscriber(array $listeners): bool
    {
        foreach ($listeners as $listener) {
            if ($listener instanceof MetadataSubscriber) {
                return true;
            }
        }

        return false;
    }
}
