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
use Doctrine\ORM\Mapping\ReflectionEmbeddedProperty;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ReflectionService;

abstract class AbstractExtensionMetadata implements ExtensionMetadataInterface
{
    protected string $name;
    /**
     * @var MetadataConfigurationInterface[]
     */
    private array $configurations = [];
    private array $reflectionFields = [];
    /**
     * @var ExtensionMetadataInterface[]
     */
    private array $embedded = [];

    public function __construct(private ClassMetadata $metadata)
    {
        $this->name = $metadata->getName();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addConfiguration(MetadataConfigurationInterface $configuration): void
    {
        $this->configurations[\get_class($configuration)] = $configuration;
    }

    public function getConfiguration(string $class): ?MetadataConfigurationInterface
    {
        return $this->configurations[$class] ?? null;
    }

    public function wakeup(ClassMetadata $metadata, ReflectionService $reflectionService): void
    {
        //called before origin wakeup method
        $this->metadata = $metadata;

        $parentFields = [];
        foreach (\array_keys($this->embedded) as $field) {
            $parentFields[$field] = $reflectionService->getAccessibleProperty($this->name, $field);
        }

        //collect only needed properties
        $mappings = [];
        foreach ($this->configurations as $configuration) {
            $mappings = \array_replace($mappings, $configuration->getMappings());
        }
        $mappings = \array_diff_key($mappings, $metadata->fieldMappings);

        foreach ($mappings as $field => $mapping) {
            if (isset($mapping['declaredField']) && isset($parentFields[$mapping['declaredField']])) {
                $this->reflectionFields[$field] = new ReflectionEmbeddedProperty(
                    $parentFields[$mapping['declaredField']],
                    $reflectionService->getAccessibleProperty($mapping['originalClass'], $mapping['originalField']),
                    $mapping['originalClass']
                );
                continue;
            }
            $this->reflectionFields[$field] = $reflectionService->getAccessibleProperty($this->name, $field);
        }
    }

    public function getOriginMetadata(): ClassMetadata
    {
        return $this->metadata;
    }

    /**
     * Sets the specified field to the specified value on the given entity.
     */
    public function setFieldValue(object $entity, string $field, mixed $value): void
    {
        if (isset($this->metadata->getPropertyAccessors()[$field])) {
            $this->metadata->setFieldValue($entity, $field, $value);

            return;
        }

        $this->reflectionFields[$field]->setValue($entity, $value);
    }

    /**
     * Gets the specified field's value off the given entity.
     */
    public function getFieldValue(object $entity, string $field): mixed
    {
        if (isset($this->metadata->getPropertyAccessors()[$field])) {
            return $this->metadata->getFieldValue($entity, $field);
        }

        return $this->reflectionFields[$field]->getValue($entity);
    }

    public function getEmbeddedMetadata(): array
    {
        return $this->embedded;
    }

    public function getEmbeddedMetadataWithConfiguration(string $class): iterable
    {
        foreach ($this->embedded as $fieldName => $metadata) {
            $config = $metadata->getConfiguration($class);
            if (null !== $config) {
                yield $fieldName => $metadata;
            }
        }
    }

    public function addEmbeddedMetadata(string $fieldName, ExtensionMetadataInterface $metadata): void
    {
        $this->embedded[$fieldName] = $metadata;
    }

    public function __serialize(): array
    {
        return [
            $this->name,
            $this->configurations,
        ];
    }

    public function __unserialize(array $serialized): void
    {
        [
            $this->name,
            $this->configurations,
        ] = $serialized;
    }
}
