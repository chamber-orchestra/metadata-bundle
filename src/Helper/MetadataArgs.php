<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\Helper;

use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use ChamberOrchestra\MetadataBundle\Mapping\ORM\MetadataConfigurationInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class MetadataArgs
{
    private ClassMetadata|null $classMetadata = null {
        get {
            $name = \method_exists($this->configuration, 'getEntityName')
                ? $this->configuration->getEntityName()
                : ClassUtils::getClass($this->entity);

            return $this->classMetadata ??= $this->entityManager->getClassMetadata($name);
        }
    }

    public function __construct(
        public readonly EntityManagerInterface $entityManager,
        public readonly ExtensionMetadataInterface $extensionMetadata,
        public readonly MetadataConfigurationInterface $configuration,
        public readonly object $entity
    ) {
    }

}