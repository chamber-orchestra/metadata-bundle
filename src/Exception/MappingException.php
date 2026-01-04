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
        return new self('Property "'.$fieldName.'" in "'.$entity.'" was already declared, but it must be declared only once');
    }

    public static function missingProperty(string $className, string $property, string $originProperty): self
    {
        return new self(\sprintf('Class "%s" has no property "%s" specified in property "%s"',
            $className,
            $property,
            $originProperty
        ));
    }

    public static function missingAnnotation(string $className, string $field, string $annotationClass): self
    {
        return new self(\sprintf('Class "%s" has no required annotation "%s" at field "%s".',
            $className,
            $annotationClass,
            $field
        ));
    }
}
