<?php

declare(strict_types=1);

namespace Tests\Fixtures\Mapping;

use ChamberOrchestra\MetadataBundle\Mapping\Driver\MappingDriverInterface;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;

final class TrackingMappingDriver implements MappingDriverInterface
{
    public static int $supportsCalls = 0;
    public static int $loadCalls = 0;

    public static function reset(): void
    {
        self::$supportsCalls = 0;
        self::$loadCalls = 0;
    }

    public function loadMetadataForClass(ExtensionMetadataInterface $extensionMetadata): void
    {
        self::$loadCalls++;
    }

    public function supports(ExtensionMetadataInterface $metadata): bool
    {
        self::$supportsCalls++;

        return true;
    }
}
