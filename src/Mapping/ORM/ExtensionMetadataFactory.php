<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\Mapping\ORM;

use ChamberOrchestra\MetadataBundle\Mapping\AbstractExtensionMetadataFactory;
use ChamberOrchestra\MetadataBundle\Mapping\Driver\MappingDriverInterface;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class ExtensionMetadataFactory extends AbstractExtensionMetadataFactory
{
    /**
     * @param iterable<MappingDriverInterface> $drivers
     */
    public function __construct(
        #[AutowireIterator('chamber_orchestra_metadata.mapping.driver')]
        private readonly iterable $drivers,
    ) {}

    protected function doLoadMetadata(ExtensionMetadataInterface $class): void
    {
        foreach ($this->drivers as $driver) {
            if ($driver->supports($class)) {
                $driver->loadMetadataForClass($class);
            }
        }
    }

    protected function newClassMetadataInstance(ClassMetadata $metadata): ExtensionMetadataInterface
    {
        return new ExtensionMetadata($metadata);
    }
}
