<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Exception;

use ChamberOrchestra\MetadataBundle\Exception\InvalidMappingAttributeException;
use ChamberOrchestra\MetadataBundle\Exception\MappingException;
use PHPUnit\Framework\TestCase;

final class InvalidMappingAttributeExceptionTest extends TestCase
{
    public function testIsInstanceOfMappingException(): void
    {
        $exception = new InvalidMappingAttributeException('test');

        self::assertInstanceOf(MappingException::class, $exception);
    }

    public function testFactoryMethodReturnsCorrectType(): void
    {
        $exception = MappingException::missingAttribute('Foo', 'bar', 'Baz');

        self::assertInstanceOf(InvalidMappingAttributeException::class, $exception);
    }
}
