<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\Exception;

use RuntimeException;

class MappingException extends RuntimeException implements ExceptionInterface
{
    public static function duplicateFieldMapping(string $entity, string $fieldName): self
    {
        return new self('Property "' . $fieldName . '" in "' . $entity . '" was already declared, but it must be declared only once');
    }

    public static function missingProperty(string $className, string $property, string $originProperty): self
    {
        return new self(\sprintf(
            'Class "%s" has no property "%s" specified in property "%s"',
            $className,
            $property,
            $originProperty
        ));
    }

    public static function missingAttribute(string $className, string $field, string $attributeClass): self
    {
        return new self(\sprintf(
            'Class "%s" has no required attribute "%s" at field "%s".',
            $className,
            $attributeClass,
            $field
        ));
    }

    public static function invalidFieldName(string $declaredField, string $originalField): self
    {
        return new self(\sprintf(
            'Embedded field names cannot contain dots. Got declaredField="%s", originalField="%s".',
            $declaredField,
            $originalField
        ));
    }
}
