<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use ChamberOrchestra\MetadataBundle\Exception\MappingException;
use PHPUnit\Framework\TestCase;

final class MappingExceptionTest extends TestCase
{
    public function testDuplicateFieldMappingMessage(): void
    {
        $exception = MappingException::duplicateFieldMapping('Foo', 'bar');

        self::assertSame('Property "bar" in "Foo" was already declared, but it must be declared only once', $exception->getMessage());
    }

    public function testMissingPropertyMessage(): void
    {
        $exception = MappingException::missingProperty('Foo', 'bar', 'baz');

        self::assertSame('Class "Foo" has no property "bar" specified in property "baz"', $exception->getMessage());
    }

    public function testMissingAttributeMessage(): void
    {
        $exception = MappingException::missingAttribute('Foo', 'bar', 'Baz');

        self::assertSame('Class "Foo" has no required attribute "Baz" at field "bar".', $exception->getMessage());
    }
}
