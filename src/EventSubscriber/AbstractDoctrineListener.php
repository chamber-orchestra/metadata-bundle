<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\EventSubscriber;

use ChamberOrchestra\MetadataBundle\Helper\MetadataArgs;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractDoctrineListener
{
    use MetadataConfigurationTrait;

    /**
     * @return MetadataArgs[]
     */
    protected function getScheduledEntityInsertions(EntityManagerInterface $em, string $class): array
    {
        return $this->collectArgs($em->getUnitOfWork()->getScheduledEntityInsertions(), $em, $class);
    }

    /**
     * @return MetadataArgs[]
     */
    protected function getScheduledEntityUpdates(EntityManagerInterface $em, string $class): array
    {
        return $this->collectArgs($em->getUnitOfWork()->getScheduledEntityUpdates(), $em, $class);
    }

    /**
     * @return MetadataArgs[]
     */
    protected function getScheduledEntityDeletions(EntityManagerInterface $em, string $class): array
    {
        return $this->collectArgs($em->getUnitOfWork()->getScheduledEntityDeletions(), $em, $class);
    }

    /**
     * @return array<string, array{mixed, mixed}|\Doctrine\ORM\PersistentCollection>
     */
    protected function getEntityChangeSet(EntityManagerInterface $em, MetadataArgs $args): array
    {
        return $em->getUnitOfWork()->getEntityChangeSet($args->entity);
    }

    /**
     * @param array<object> $entities
     *
     * @return MetadataArgs[]
     */
    private function collectArgs(array $entities, EntityManagerInterface $em, string $class): array
    {
        $reader = $this->requireReader();
        $result = [];
        foreach ($entities as $entity) {
            /** @var class-string $entityClass */
            $entityClass = ClassUtils::getClass($entity);
            $metadata = $reader->getExtensionMetadata($em, $entityClass);
            $configuration = $metadata->getConfiguration($class);
            if (null !== $configuration) {
                $result[] = new MetadataArgs($em, $metadata, $configuration, $entity);
            }
        }

        return $result;
    }
}
