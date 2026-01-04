<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\Mapping\Driver;

use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;

interface MappingDriverInterface
{
    /**
     * Loads the metadata for the specified class into the provided container.
     */
    public function loadMetadataForClass(ExtensionMetadataInterface $extensionMetadata): void;

    /**
     * Returns whether the class with the specified name should have its metadata loaded.
     * This is only the case if it is either mapped as an Entity or a MappedSuperclass.
     */
    public function supports(ExtensionMetadataInterface $metadata): bool;
}
