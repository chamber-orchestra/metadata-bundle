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
use ChamberOrchestra\MetadataBundle\Mapping\ORM\EntityNameAwareInterface;
use ChamberOrchestra\MetadataBundle\Mapping\ORM\MetadataConfigurationInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class MetadataArgs
{
    /** @var ClassMetadata<object>|null */
    private ?ClassMetadata $resolvedClassMetadata = null;

    public function __construct(
        public readonly EntityManagerInterface $entityManager,
        public readonly ExtensionMetadataInterface $extensionMetadata,
        public readonly MetadataConfigurationInterface $configuration,
        public readonly object $entity
    ) {
    }

    /**
     * @return ClassMetadata<object>
     */
    public function getClassMetadata(): ClassMetadata
    {
        if (null !== $this->resolvedClassMetadata) {
            return $this->resolvedClassMetadata;
        }

        $name = $this->configuration instanceof EntityNameAwareInterface
            ? $this->configuration->getEntityName()
            : ClassUtils::getClass($this->entity);

        return $this->resolvedClassMetadata = $this->entityManager->getClassMetadata($name);
    }
}
