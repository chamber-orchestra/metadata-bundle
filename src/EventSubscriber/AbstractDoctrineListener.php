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
        return \array_map(fn(object $entity) => $this->args($em, $entity, $class), \array_filter(
            $em->getUnitOfWork()->getScheduledEntityInsertions(),
            fn(object $entity): bool => null !== $this->getConfiguration($em, $entity, $class)
        ));
    }

    /**
     * @return MetadataArgs[]
     */
    protected function getScheduledEntityUpdates(EntityManagerInterface $em, string $class): array
    {
        return \array_map(fn(object $entity) => $this->args($em, $entity, $class), \array_filter(
            $em->getUnitOfWork()->getScheduledEntityUpdates(),
            fn(object $entity): bool => null !== $this->getConfiguration($em, $entity, $class)
        ));
    }

    /**
     * @return MetadataArgs[]
     */
    protected function getScheduledEntityDeletions(EntityManagerInterface $em, string $class): array
    {
        return \array_map(fn(object $entity) => $this->args($em, $entity, $class), \array_filter(
            $em->getUnitOfWork()->getScheduledEntityDeletions(),
            fn(object $entity): bool => null !== $this->getConfiguration($em, $entity, $class)
        ));
    }

    protected function args(EntityManagerInterface $em, object $entity, string $class): MetadataArgs
    {
        $metadata = $this->reader->getExtensionMetadata($em, ClassUtils::getClass($entity));

        return new MetadataArgs($em, $metadata, $metadata->getConfiguration($class), $entity);
    }
}
