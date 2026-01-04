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

    public function supports(ExtensionMetadataInterface $metadata): bool
    {
        if ($this->supportsEmbedded()) {
            if (\array_any($metadata->getEmbeddedMetadata(), fn(ExtensionMetadataInterface $embeddedMetadata) => $this->supports($embeddedMetadata))) {
                return true;
            }
        }

        return $this->supportsByClassAttribute($metadata) && $this->supportsByPropertyAnnotation($metadata);
    }

    protected function getClassAnnotation(): string|null
    {
        return null;
    }

    protected function getPropertyAnnotation(): string|null
    {
        return null;
    }

    protected function supportsEmbedded(): bool
    {
        return false;
    }

    private function supportsByClassAttribute(ExtensionMetadataInterface $metadata): bool
    {
        if (null === ($annotation = $this->getClassAnnotation())) {
            return true;
        }

        return null !== $this->reader->getClassAttribute($metadata->getOriginMetadata()->getReflectionClass(), $annotation);
    }

    private function supportsByPropertyAnnotation(ExtensionMetadataInterface $metadata): bool
    {
        if (null === ($annotation = $this->getPropertyAnnotation())) {
            return true;
        }

        $reflection = $metadata->getOriginMetadata()->getReflectionClass();
        $properties = $reflection->getProperties();

        return \array_any($properties, fn($property) => null !== $this->reader->getPropertyAttribute($property, $annotation));
    }
}
