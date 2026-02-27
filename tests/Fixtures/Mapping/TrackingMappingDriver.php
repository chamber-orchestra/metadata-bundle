<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Mapping;

use ChamberOrchestra\MetadataBundle\Mapping\Driver\MappingDriverInterface;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;

final class TrackingMappingDriver implements MappingDriverInterface
{
    public int $supportsCalls = 0;
    public int $loadCalls = 0;

    public function loadMetadataForClass(ExtensionMetadataInterface $extensionMetadata): void
    {
        ++$this->loadCalls;
    }

    public function supports(ExtensionMetadataInterface $metadata): bool
    {
        ++$this->supportsCalls;

        return true;
    }

    public static function getPriority(): int
    {
        return 0;
    }
}
