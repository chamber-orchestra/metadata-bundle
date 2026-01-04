<?php

declare(strict_types=1);

namespace Tests\Fixtures\Mapping;

use ChamberOrchestra\MetadataBundle\Mapping\Driver\MappingDriverInterface;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;

class DummyMappingDriver implements MappingDriverInterface
{
    public function loadMetadataForClass(ExtensionMetadataInterface $extensionMetadata): void
    {
    }

    public function supports(ExtensionMetadataInterface $metadata): bool
    {
        return false;
    }
}
