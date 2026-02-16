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
     * @return array<string, ExtensionMetadataInterface>
     */
    public function getEmbeddedMetadata(): array;

    /**
     * @return iterable<string, ExtensionMetadataInterface>
     */
    public function getEmbeddedMetadataWithConfiguration(string $class): iterable;

    public function addEmbeddedMetadata(string $fieldName, ExtensionMetadataInterface $metadata): void;

    public function getConfiguration(string $class): ?MetadataConfigurationInterface;

    public function addConfiguration(MetadataConfigurationInterface $configuration): void;

    /**
     * Restores runtime state after deserialization.
     *
     * Initializes ClassMetadata reference and reflection fields for this instance only.
     * Does NOT recursively wake embedded metadata — that is the responsibility of
     * AbstractExtensionMetadataFactory::wakeup().
     *
     * @param ClassMetadata<object> $metadata
     */
    public function wakeup(ClassMetadata $metadata, ReflectionService $reflectionService): void;

    /**
     * @return ClassMetadata<object>
     */
    public function getOriginMetadata(): ClassMetadata;

    public function setFieldValue(object $entity, string $field, mixed $value): void;

    public function getFieldValue(object $entity, string $field): mixed;
}
