<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\Mapping\ORM;

interface MetadataConfigurationInterface
{
    /**
     * @param array<string, mixed> $mapping
     */
    public function mapField(string $fieldName, array $mapping = []): void;

    /**
     * @param array<string, mixed> $mapping
     */
    public function mapEmbeddedField(string $class, string $declaredField, string $originalField, array $mapping = []): void;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getMappings(): array;

    /**
     * Checks if the given field is a mapped property for this class.
     */
    public function hasMapping(string $fieldName): bool;

    /**
     * A numerically indexed list of field names of this persistent class.
     *
     * This array includes identifier fields if present on this class.
     *
     * @return list<string>
     */
    public function getFieldNames(): array;

    /**
     * @return array<string, mixed>
     */
    public function getMapping(string $fieldName): array;
}
