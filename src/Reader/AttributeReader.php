<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\Reader;

use ChamberOrchestra\MetadataBundle\Exception\LogicException;
use Doctrine\ORM\Mapping\MappingAttribute;

class AttributeReader
{
    /**
     * @param \ReflectionClass<object> $class
     *
     * @return array<class-string, MappingAttribute>
     */
    public function getClassAttributes(\ReflectionClass $class): array
    {
        return $this->convertAttributes($class->getAttributes(MappingAttribute::class, \ReflectionAttribute::IS_INSTANCEOF));
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    public function getClassAttribute(\ReflectionClass $class, string $attributeName): ?MappingAttribute
    {
        return $this->getClassAttributes($class)[$attributeName] ?? null;
    }

    /**
     * @return array<class-string, MappingAttribute>
     */
    public function getMethodAttributes(\ReflectionMethod $method): array
    {
        return $this->convertAttributes($method->getAttributes(MappingAttribute::class, \ReflectionAttribute::IS_INSTANCEOF));
    }

    public function getMethodAttribute(\ReflectionMethod $method, string $attributeName): ?MappingAttribute
    {
        return $this->getMethodAttributes($method)[$attributeName] ?? null;
    }

    /**
     * @return array<class-string, MappingAttribute>
     */
    public function getPropertyAttributes(\ReflectionProperty $property): array
    {
        return $this->convertAttributes($property->getAttributes(MappingAttribute::class, \ReflectionAttribute::IS_INSTANCEOF));
    }

    public function getPropertyAttribute(\ReflectionProperty $property, string $attributeName): ?MappingAttribute
    {
        return $this->getPropertyAttributes($property)[$attributeName] ?? null;
    }

    /**
     * @param \ReflectionAttribute<MappingAttribute>[] $reflectionAttributes
     *
     * @return array<class-string, MappingAttribute>
     */
    private function convertAttributes(array $reflectionAttributes): array
    {
        $result = [];
        foreach ($reflectionAttributes as $attr) {
            $name = $attr->getName();
            if (isset($result[$name])) {
                throw new LogicException(\sprintf(
                    'Duplicate attribute "%s" detected. This reader only supports single instances of each attribute type.',
                    $name
                ));
            }
            $result[$name] = $attr->newInstance();
        }

        return $result;
    }
}
