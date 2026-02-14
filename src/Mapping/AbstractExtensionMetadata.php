<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\Mapping;

use ChamberOrchestra\MetadataBundle\Exception\LogicException;
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
        if ($metadata->getName() !== $this->name) {
            throw new LogicException(\sprintf(
                'Metadata class mismatch during wakeup. Expected "%s", got "%s".',
                $this->name,
                $metadata->getName()
            ));
        }

        $this->metadata = $metadata;
        $this->reflectionFields = [];

        // Collect only needed properties, detecting cross-configuration duplicates
        $mappings = [];
        foreach ($this->configurations as $configuration) {
            foreach ($configuration->getMappings() as $field => $mapping) {
                if (isset($mappings[$field])) {
                    throw new LogicException(\sprintf(
                        'Field "%s" is mapped by multiple configurations in class "%s". Each field may only be mapped once.',
                        $field,
                        $this->name
                    ));
                }
                $mappings[$field] = $mapping;
            }
        }
        if ($metadata->fieldMappings) {
            $mappings = \array_diff_key($mappings, $metadata->fieldMappings);
        }

        // Lazily resolve embedded parent fields only for those actually used by mappings
        $parentFields = [];

        foreach ($mappings as $field => $mapping) {
            if (isset($mapping['declaredField']) && !isset($this->embedded[$mapping['declaredField']])) {
                throw new LogicException(\sprintf(
                    'Embedded field "%s" is referenced by configuration but no embedded metadata exists for class "%s".',
                    $mapping['declaredField'],
                    $this->name
                ));
            }

            if (isset($mapping['declaredField']) && isset($this->embedded[$mapping['declaredField']])) {
                $declaredField = $mapping['declaredField'];
                if (!isset($parentFields[$declaredField])) {
                    $parentFields[$declaredField] = $reflectionService->getAccessibleProperty($this->name, $declaredField);
                }
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
        if (!isset($this->metadata)) {
            throw new LogicException(\sprintf(
                'Origin metadata not available for "%s". Call wakeup() after deserialization.',
                $this->name
            ));
        }

        return $this->metadata;
    }

    /**
     * Sets the specified field to the specified value on the given entity.
     */
    public function setFieldValue(object $entity, string $field, mixed $value): void
    {
        if (!isset($this->metadata)) {
            throw new LogicException(\sprintf(
                'Extension metadata for "%s" is not initialized. Call wakeup() before accessing field values.',
                $this->name
            ));
        }

        if (isset($this->metadata->getPropertyAccessors()[$field])) {
            $this->metadata->setFieldValue($entity, $field, $value);

            return;
        }

        if (!isset($this->reflectionFields[$field])) {
            throw new LogicException(\sprintf(
                'Field "%s" is not mapped in extension metadata for class "%s".',
                $field,
                $this->name
            ));
        }

        $this->reflectionFields[$field]->setValue($entity, $value);
    }

    /**
     * Gets the specified field's value off the given entity.
     */
    public function getFieldValue(object $entity, string $field): mixed
    {
        if (!isset($this->metadata)) {
            throw new LogicException(\sprintf(
                'Extension metadata for "%s" is not initialized. Call wakeup() before accessing field values.',
                $this->name
            ));
        }

        if (isset($this->metadata->getPropertyAccessors()[$field])) {
            return $this->metadata->getFieldValue($entity, $field);
        }

        if (!isset($this->reflectionFields[$field])) {
            throw new LogicException(\sprintf(
                'Field "%s" is not mapped in extension metadata for class "%s".',
                $field,
                $this->name
            ));
        }

        return $this->reflectionFields[$field]->getValue($entity);
    }

    public function getEmbeddedMetadata(): array
    {
        return $this->embedded;
    }

    public function getEmbeddedMetadataWithConfiguration(string $class): iterable
    {
        if (!isset($this->metadata)) {
            throw new LogicException(\sprintf(
                'Extension metadata for "%s" is not initialized. Call wakeup() before accessing embedded metadata.',
                $this->name
            ));
        }

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
            $this->embedded,
        ];
    }

    public function __unserialize(array $serialized): void
    {
        [
            $this->name,
            $this->configurations,
            $this->embedded,
        ] = $serialized;
    }
}
