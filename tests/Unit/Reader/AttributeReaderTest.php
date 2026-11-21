<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Reader;

use ChamberOrchestra\MetadataBundle\Reader\AttributeReader;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Attributes\ExampleClassAttribute;
use Tests\Fixtures\Attributes\ExamplePropertyAttribute;
use Tests\Fixtures\Attributes\RepeatableClassAttribute;
use Tests\Fixtures\Entity\ClassAttributedEntity;
use Tests\Fixtures\Entity\PropertyAttributedEntity;
use Tests\Fixtures\Entity\RepeatedAttributesEntity;
use Tests\Fixtures\Entity\SimpleEntity;

final class AttributeReaderTest extends TestCase
{
    public function testReadsClassAttributes(): void
    {
        $reader = new AttributeReader();
        $reflection = new \ReflectionClass(ClassAttributedEntity::class);

        $attributes = $reader->getClassAttributes($reflection);

        self::assertNotEmpty($attributes);
        self::assertInstanceOf(ExampleClassAttribute::class, $reader->getClassAttribute($reflection, ExampleClassAttribute::class));
    }

    public function testReadsPropertyAttributes(): void
    {
        $reader = new AttributeReader();
        $reflection = new \ReflectionProperty(PropertyAttributedEntity::class, 'id');

        $attributes = $reader->getPropertyAttributes($reflection);

        self::assertNotEmpty($attributes);
        self::assertInstanceOf(ExamplePropertyAttribute::class, $reader->getPropertyAttribute($reflection, ExamplePropertyAttribute::class));
    }

    public function testReturnsNullForMissingClassAttribute(): void
    {
        $reader = new AttributeReader();
        $reflection = new \ReflectionClass(SimpleEntity::class);

        self::assertNull($reader->getClassAttribute($reflection, ExampleClassAttribute::class));
    }

    public function testReturnsNullForMissingPropertyAttribute(): void
    {
        $reader = new AttributeReader();
        $reflection = new \ReflectionProperty(SimpleEntity::class, 'name');

        self::assertNull($reader->getPropertyAttribute($reflection, ExamplePropertyAttribute::class));
    }

    public function testReturnsEmptyArrayForClassWithNoAttributes(): void
    {
        $reader = new AttributeReader();
        $reflection = new \ReflectionClass(SimpleEntity::class);

        self::assertSame([], $reader->getClassAttributes($reflection));
    }

    public function testARepeatedAttributeIsReadOnceAndDoesNotHideTheOthers(): void
    {
        $reader = new AttributeReader();
        $reflection = new \ReflectionClass(RepeatedAttributesEntity::class);

        $repeated = $reader->getClassAttribute($reflection, RepeatableClassAttribute::class);

        self::assertInstanceOf(RepeatableClassAttribute::class, $repeated);
        self::assertSame('first', $repeated->name, 'the first of a repeated attribute is kept');
        self::assertInstanceOf(ExampleClassAttribute::class, $reader->getClassAttribute($reflection, ExampleClassAttribute::class));
    }
}
