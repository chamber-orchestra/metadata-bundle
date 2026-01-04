<?php

declare(strict_types=1);

namespace Tests\Unit\Reader;

use ChamberOrchestra\MetadataBundle\Reader\AttributeReader;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Attributes\ExampleClassAttribute;
use Tests\Fixtures\Attributes\ExamplePropertyAttribute;
use Tests\Fixtures\Entity\ClassAttributedEntity;
use Tests\Fixtures\Entity\PropertyAttributedEntity;

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
}
