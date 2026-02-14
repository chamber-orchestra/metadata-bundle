<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\EventSubscriber;

use ChamberOrchestra\MetadataBundle\Mapping\MetadataReader;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::loadClassMetadata, priority: -100)]
readonly class MetadataSubscriber
{
    public function __construct(private MetadataReader $metadataReader)
    {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $this->metadataReader->loadExtensionMetadata($eventArgs->getEntityManager(), $eventArgs->getClassMetadata());
    }
}
