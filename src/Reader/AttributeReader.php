<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\Reader;

use Doctrine\ORM\Mapping\MappingAttribute;

class AttributeReader
{
    private \Doctrine\ORM\Mapping\Driver\AttributeReader $reader;

    public function __construct()
    {
        $this->reader = new \Doctrine\ORM\Mapping\Driver\AttributeReader();
    }

    public function getClassAttributes(\ReflectionClass $class): array
    {
        return $this->reader->getClassAttributes($class);
    }

    public function getClassAttribute(\ReflectionClass $class, $attributeName): MappingAttribute|null
    {
        return \array_find($this->getClassAttributes($class), fn($a) => $a instanceof $attributeName);

    }

    public function getMethodAttributes(\ReflectionMethod $method): array
    {
        return $this->reader->getMethodAttributes($method);
    }

    public function getMethodAttribute(\ReflectionMethod $method, $attributeName): MappingAttribute|null
    {
        return $this->getMethodAttributes($method)[$attributeName] ?? null;
    }

    public function getPropertyAttributes(\ReflectionProperty $property): array
    {
        return $this->reader->getPropertyAttributes($property);
    }

    public function getPropertyAttribute(\ReflectionProperty $property, $attributeName): MappingAttribute|null
    {
        return $this->reader->getPropertyAttribute($property, $attributeName);
    }
}