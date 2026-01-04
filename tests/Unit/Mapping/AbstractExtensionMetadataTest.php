<?php

declare(strict_types=1);

namespace Tests\Unit\Mapping;

use ChamberOrchestra\MetadataBundle\Mapping\ORM\ExtensionMetadata;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Entity\EmbeddedValue;
use Tests\Fixtures\Entity\EntityWithEmbedded;
use Tests\Fixtures\Entity\SimpleEntity;
use Tests\Fixtures\Mapping\TestMetadataConfiguration;

final class AbstractExtensionMetadataTest extends TestCase
{
    public function testConfigurationRegistrationAndLookup(): void
    {
        $metadata = new ExtensionMetadata(new ClassMetadata(SimpleEntity::class));
        $configuration = new TestMetadataConfiguration();

        $metadata->addConfiguration($configuration);

        self::assertSame($configuration, $metadata->getConfiguration(TestMetadataConfiguration::class));
    }

    public function testEmbeddedMetadataFilteringByConfiguration(): void
    {
        $metadata = new ExtensionMetadata(new ClassMetadata(EntityWithEmbedded::class));

        $embeddedWithConfig = new ExtensionMetadata(new ClassMetadata(EmbeddedValue::class));
        $embeddedWithConfig->addConfiguration(new TestMetadataConfiguration());

        $embeddedWithoutConfig = new ExtensionMetadata(new ClassMetadata(SimpleEntity::class));

        $metadata->addEmbeddedMetadata('with', $embeddedWithConfig);
        $metadata->addEmbeddedMetadata('without', $embeddedWithoutConfig);

        $filtered = 
            \iterator_to_array($metadata->getEmbeddedMetadataWithConfiguration(TestMetadataConfiguration::class));

        self::assertArrayHasKey('with', $filtered);
        self::assertArrayNotHasKey('without', $filtered);
    }

    public function testWakeupRegistersReflectionFieldsAndAccessors(): void
    {
        $entityMetadata = new ClassMetadata(EntityWithEmbedded::class);
        $metadata = new ExtensionMetadata($entityMetadata);

        $configuration = new TestMetadataConfiguration();
        $configuration->mapField('name');
        $configuration->mapEmbeddedField(EmbeddedValue::class, 'embedded', 'value');

        $metadata->addConfiguration($configuration);
        $metadata->addEmbeddedMetadata('embedded', new ExtensionMetadata(new ClassMetadata(EmbeddedValue::class)));

        $metadata->wakeup($entityMetadata, new RuntimeReflectionService());

        $entity = new EntityWithEmbedded();

        $metadata->setFieldValue($entity, 'name', 'hello');
        $metadata->setFieldValue($entity, 'embedded.value', 'world');

        self::assertSame('hello', $metadata->getFieldValue($entity, 'name'));
        self::assertSame('world', $metadata->getFieldValue($entity, 'embedded.value'));
        self::assertSame('world', $entity->getEmbedded()->getValue());
    }

    public function testUsesPropertyAccessorsWhenAvailable(): void
    {
        $metadata = new class(AccessorEntity::class) extends ClassMetadata {
            public int $setCalls = 0;
            public int $getCalls = 0;

            public function getPropertyAccessors(): array
            {
                return ['name' => true];
            }

            public function setFieldValue(object $entity, string $field, mixed $value): void
            {
                $this->setCalls++;
                $entity->$field = $value;
            }

            public function getFieldValue(object $entity, string $field): mixed
            {
                $this->getCalls++;

                return $entity->$field;
            }
        };

        $extensionMetadata = new ExtensionMetadata($metadata);
        $entity = new AccessorEntity();

        $extensionMetadata->setFieldValue($entity, 'name', 'accessed');

        self::assertSame('accessed', $extensionMetadata->getFieldValue($entity, 'name'));
        self::assertSame(1, $metadata->setCalls);
        self::assertSame(1, $metadata->getCalls);
    }
}

final class AccessorEntity
{
    public string $name = '';
}
