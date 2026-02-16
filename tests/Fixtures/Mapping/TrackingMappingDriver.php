<?php

declare(strict_types=1);

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
}
