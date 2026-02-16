<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\Mapping\ORM;

use ChamberOrchestra\MetadataBundle\Exception\MappingException;

abstract class AbstractMetadataConfiguration implements MetadataConfigurationInterface
{
    /** @var array<string, array<string, mixed>> */
    protected array $mappings = [];

    public function mapField(string $fieldName, array $mapping = []): void
    {
        $this->assertFieldNotMapped($fieldName);
        $this->mappings[$fieldName] = \array_replace($mapping, ['fieldName' => $fieldName]);
    }

    public function mapEmbeddedField(string $class, string $declaredField, string $originalField, array $mapping = []): void
    {
        if (\str_contains($declaredField, '.') || \str_contains($originalField, '.')) {
            throw MappingException::invalidFieldName($declaredField, $originalField);
        }

        $this->mapField($declaredField . '.' . $originalField, \array_replace($mapping, [
            'declaredField' => $declaredField,
            'originalField' => $originalField,
            'originalClass' => $class,
        ]));
    }

    public function hasMapping(string $fieldName): bool
    {
        return isset($this->mappings[$fieldName]);
    }

    public function getMapping(string $fieldName): array
    {
        if (!isset($this->mappings[$fieldName])) {
            throw MappingException::missingProperty(static::class, $fieldName, $fieldName);
        }

        return $this->mappings[$fieldName];
    }

    public function getMappings(): array
    {
        return $this->mappings;
    }

    public function getFieldNames(): array
    {
        return \array_keys($this->mappings);
    }

    public function __serialize(): array
    {
        return ['mappings' => $this->mappings];
    }

    /**
     * @param array{mappings: array<string, array<string, mixed>>} $data
     */
    public function __unserialize(array $data): void
    {
        ['mappings' => $this->mappings] = $data;
    }

    protected function assertFieldNotMapped(string $fieldName): void
    {
        if (isset($this->mappings[$fieldName])) {
            throw MappingException::duplicateFieldMapping(static::class, $fieldName);
        }
    }
}
