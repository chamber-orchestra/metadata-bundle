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
use ChamberOrchestra\MetadataBundle\Mapping\ORM\MetadataConfigurationInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait MetadataConfigurationTrait
{
    protected MetadataReader|null $reader = null;

    #[Required]
    public function setMetadataReader(MetadataReader $reader): void
    {
        $this->reader = $reader;
    }

    protected function getConfiguration(EntityManagerInterface $em, object $entity, string $class): MetadataConfigurationInterface|null
    {
        return $this->reader->getExtensionMetadata($em, ClassUtils::getClass($entity))->getConfiguration($class);
    }
}
