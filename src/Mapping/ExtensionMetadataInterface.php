<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\Mapping;

use ChamberOrchestra\MetadataBundle\Mapping\ORM\MetadataConfigurationInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ReflectionService;

interface ExtensionMetadataInterface
{
    /**
     * Gets the fully-qualified class name of this persistent class.
     */
    public function getName(): string;

    /**
     * @return ExtensionMetadataInterface[]
     */
    public function getEmbeddedMetadata(): array;

    /**
     * @return ExtensionMetadataInterface[]
     */
    public function getEmbeddedMetadataWithConfiguration(string $class): iterable;

    public function addEmbeddedMetadata(string $fieldName, ExtensionMetadataInterface $metadata): void;

    public function getConfiguration(string $class): ?MetadataConfigurationInterface;

    public function addConfiguration(MetadataConfigurationInterface $configuration): void;

    public function wakeup(ClassMetadata $metadata, ReflectionService $reflectionService): void;

    public function getOriginMetadata(): ClassMetadata;

    public function setFieldValue(object $entity, string $field, $value): void;

    public function getFieldValue(object $entity, string $field);
}
