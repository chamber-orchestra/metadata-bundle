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
use ChamberOrchestra\MetadataBundle\Reader\AttributeReader;

abstract class AbstractMappingDriver implements MappingDriverInterface
{
    public function __construct(protected readonly AttributeReader $reader)
    {
    }

    public static function getPriority(): int
    {
        return 0;
    }

    /**
     * Returns true if this driver should process the given metadata.
     *
     * The entity is supported if:
     *   1. It has both the required class-level AND property-level attributes
     *      (when both getClassAttribute() and getPropertyAttribute() are overridden), OR
     *   2. Embedded support is enabled and any embedded metadata is supported.
     *
     * When getClassAttribute() or getPropertyAttribute() returns null,
     * that check is treated as unconditionally passing.
     */
    public function supports(ExtensionMetadataInterface $metadata): bool
    {
        return $this->doSupports($metadata, []);
    }

    /**
     * @param array<string, true> $visited
     */
    private function doSupports(ExtensionMetadataInterface $metadata, array $visited): bool
    {
        if ($this->supportsEmbedded()) {
            $className = $metadata->getName();
            if (isset($visited[$className])) {
                return false;
            }
            $visited[$className] = true;

            if (\array_any($metadata->getEmbeddedMetadata(), fn (ExtensionMetadataInterface $embeddedMetadata) => $this->doSupports($embeddedMetadata, $visited))) {
                return true;
            }
        }

        return $this->supportsByClassAttribute($metadata) && $this->supportsByPropertyAttribute($metadata);
    }

    protected function getClassAttribute(): ?string
    {
        return null;
    }

    protected function getPropertyAttribute(): ?string
    {
        return null;
    }

    protected function supportsEmbedded(): bool
    {
        return false;
    }

    private function supportsByClassAttribute(ExtensionMetadataInterface $metadata): bool
    {
        if (null === ($attribute = $this->getClassAttribute())) {
            return true;
        }

        $reflection = $metadata->getOriginMetadata()->getReflectionClass();

        return null !== $this->reader->getClassAttribute($reflection, $attribute);
    }

    private function supportsByPropertyAttribute(ExtensionMetadataInterface $metadata): bool
    {
        if (null === ($attribute = $this->getPropertyAttribute())) {
            return true;
        }

        $reflection = $metadata->getOriginMetadata()->getReflectionClass();

        foreach ($reflection->getProperties() as $property) {
            if (null !== $this->reader->getPropertyAttribute($property, $attribute)) {
                return true;
            }
        }

        return false;
    }
}
