<?php

declare(strict_types=1);

namespace Tests\Unit\Mapping\Driver;

use ChamberOrchestra\MetadataBundle\Mapping\Driver\AbstractMappingDriver;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use ChamberOrchestra\MetadataBundle\Mapping\ORM\ExtensionMetadata;
use ChamberOrchestra\MetadataBundle\Reader\AttributeReader;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Attributes\ExampleClassAttribute;
use Tests\Fixtures\Attributes\ExamplePropertyAttribute;
use Tests\Fixtures\Entity\ClassAttributedEntity;
use Tests\Fixtures\Entity\PropertyAttributedEntity;
use Tests\Fixtures\Entity\SimpleEntity;

final class AbstractMappingDriverTest extends TestCase
{
    public function testSupportsByClassAttribute(): void
    {
        $driver = new class(new AttributeReader()) extends AbstractMappingDriver {
            public function loadMetadataForClass(ExtensionMetadataInterface $extensionMetadata): void
            {
            }

            protected function getClassAttribute(): ?string
            {
                return ExampleClassAttribute::class;
            }
        };

        $metadata = $this->createMetadata(ClassAttributedEntity::class);

        self::assertTrue($driver->supports($metadata));
    }

    public function testSupportsByPropertyAttribute(): void
    {
        $driver = new class(new AttributeReader()) extends AbstractMappingDriver {
            public function loadMetadataForClass(ExtensionMetadataInterface $extensionMetadata): void
            {
            }

            protected function getPropertyAttribute(): ?string
            {
                return ExamplePropertyAttribute::class;
            }
        };

        $metadata = $this->createMetadata(PropertyAttributedEntity::class);

        self::assertTrue($driver->supports($metadata));
    }

    public function testDoesNotSupportWithoutRequiredAttribute(): void
    {
        $driver = new class(new AttributeReader()) extends AbstractMappingDriver {
            public function loadMetadataForClass(ExtensionMetadataInterface $extensionMetadata): void
            {
            }

            protected function getPropertyAttribute(): ?string
            {
                return ExamplePropertyAttribute::class;
            }
        };

        $metadata = $this->createMetadata(SimpleEntity::class);

        self::assertFalse($driver->supports($metadata));
    }

    public function testSupportsEmbeddedMetadataWhenEnabled(): void
    {
        $driver = new class(new AttributeReader()) extends AbstractMappingDriver {
            public function loadMetadataForClass(ExtensionMetadataInterface $extensionMetadata): void
            {
            }

            protected function getClassAttribute(): ?string
            {
                return ExampleClassAttribute::class;
            }

            protected function supportsEmbedded(): bool
            {
                return true;
            }
        };

        $parent = $this->createMetadata(SimpleEntity::class);
        $embedded = $this->createMetadata(ClassAttributedEntity::class);

        $parent->addEmbeddedMetadata('embedded', $embedded);

        self::assertTrue($driver->supports($parent));
    }

    private function createMetadata(string $class): ExtensionMetadata
    {
        $classMetadata = new ClassMetadata($class);
        $classMetadata->initializeReflection(new RuntimeReflectionService());

        return new ExtensionMetadata($classMetadata);
    }
}
