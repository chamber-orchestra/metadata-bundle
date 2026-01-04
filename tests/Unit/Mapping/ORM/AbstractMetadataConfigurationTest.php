<?php

declare(strict_types=1);

namespace Tests\Unit\Mapping\ORM;

use ChamberOrchestra\MetadataBundle\Exception\MappingException;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Mapping\TestMetadataConfiguration;

final class AbstractMetadataConfigurationTest extends TestCase
{
    public function testMapFieldAndAccessors(): void
    {
        $configuration = new TestMetadataConfiguration();
        $configuration->mapField('name', ['type' => 'string']);

        self::assertTrue($configuration->hasMapping('name'));
        self::assertSame(['type' => 'string', 'fieldName' => 'name'], $configuration->getMapping('name'));
        self::assertSame(['name'], $configuration->getFieldNames());
        self::assertSame(['name' => ['type' => 'string', 'fieldName' => 'name']], $configuration->getMappings());
    }

    public function testMapEmbeddedFieldAddsMetadata(): void
    {
        $configuration = new TestMetadataConfiguration();
        $configuration->mapEmbeddedField('Foo', 'embedded', 'value', ['type' => 'string']);

        $mapping = $configuration->getMapping('embedded.value');

        self::assertSame('embedded', $mapping['declaredField']);
        self::assertSame('value', $mapping['originalField']);
        self::assertSame('Foo', $mapping['originalClass']);
        self::assertSame('embedded.value', $mapping['fieldName']);
    }

    public function testDuplicateMappingThrowsException(): void
    {
        $configuration = new TestMetadataConfiguration();
        $configuration->mapField('name');

        $this->expectException(MappingException::class);

        $configuration->mapField('name');
    }

    public function testGetMappingForMissingFieldReturnsNull(): void
    {
        $configuration = new TestMetadataConfiguration();

        self::assertFalse($configuration->hasMapping('missing'));
        self::assertSame([], $configuration->getMapping('missing'));
    }

    public function testSerializationRoundTrip(): void
    {
        $configuration = new TestMetadataConfiguration();
        $configuration->mapField('name', ['type' => 'string']);

        $serialized = $configuration->__serialize();

        $restored = new TestMetadataConfiguration();
        $restored->__unserialize($serialized);

        self::assertSame($configuration->getMappings(), $restored->getMappings());
    }
}
