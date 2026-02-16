[![PHP Composer](https://github.com/chamber-orchestra/metadata-bundle/actions/workflows/php.yml/badge.svg)](https://github.com/chamber-orchestra/metadata-bundle/actions/workflows/php.yml)
[![PHPStan Level max](https://img.shields.io/badge/PHPStan-level%20max-brightgreen.svg)](https://phpstan.org/)
[![PHP 8.5](https://img.shields.io/badge/PHP-8.5-777BB4.svg)](https://www.php.net/)
[![Symfony 8.0](https://img.shields.io/badge/Symfony-8.0-000000.svg)](https://symfony.com/)
[![Doctrine ORM 3](https://img.shields.io/badge/Doctrine%20ORM-3-orange.svg)](https://www.doctrine-project.org/)
[![Latest Stable Version](https://poser.pugx.org/chamber-orchestra/metadata-bundle/v)](https://packagist.org/packages/chamber-orchestra/metadata-bundle)
[![License](https://poser.pugx.org/chamber-orchestra/metadata-bundle/license)](https://packagist.org/packages/chamber-orchestra/metadata-bundle)

# ChamberOrchestra Metadata Bundle

A Symfony bundle for extending Doctrine ORM entities with custom attribute-driven metadata. Define PHP attributes on your entities and let autoconfigured mapping drivers turn them into cacheable, serializable metadata — with full support for embedded classes and multiple entity managers.

## Features

- **PHP attribute-based mapping** — define custom metadata using native PHP 8 attributes on entity classes and properties
- **Cacheable metadata** — multi-level cache (PSR-6 + in-memory) with serialization support for production performance
- **Embedded entity support** — automatic recursive metadata resolution for Doctrine embeddables with lazy field initialization
- **Autoconfigured mapping drivers** — implement `MappingDriverInterface` and drivers are auto-tagged via Symfony DI
- **Doctrine event integration** — hooks into `loadClassMetadata` to load extension metadata alongside Doctrine's own metadata
- **Multiple EntityManager support** — cache isolation per EntityManager via `spl_object_id` scoping
- **Zero configuration** — install the bundle and start writing drivers; no YAML/XML configuration required

## Installation

```sh
composer require chamber-orchestra/metadata-bundle
```

## Quick Start

### 1. Define a Mapping Driver

Create a mapping driver by extending `AbstractMappingDriver`. Override `getClassAttribute()` or `getPropertyAttribute()` to declare which PHP attributes your extension requires:

```php
use ChamberOrchestra\MetadataBundle\Mapping\Driver\AbstractMappingDriver;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;

class TimestampableDriver extends AbstractMappingDriver
{
    protected function getClassAttribute(): string|null
    {
        return Timestampable::class;
    }

    public function loadMetadataForClass(ExtensionMetadataInterface $extensionMetadata): void
    {
        // Read attributes and populate your metadata configuration
    }
}
```

Drivers implementing `MappingDriverInterface` are automatically tagged and registered by the bundle.

### 2. Create a Metadata Factory

Extend `AbstractExtensionMetadataFactory` to define how your extension metadata is created and loaded:

```php
use ChamberOrchestra\MetadataBundle\Mapping\AbstractExtensionMetadataFactory;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;

class TimestampableMetadataFactory extends AbstractExtensionMetadataFactory
{
    protected function newClassMetadataInstance(ClassMetadata $metadata): ExtensionMetadataInterface
    {
        return new ExtensionMetadata($metadata);
    }

    protected function doLoadMetadata(ExtensionMetadataInterface $class): void
    {
        // Delegate to your mapping drivers
    }
}
```

### 3. React to Doctrine Events

Use `AbstractDoctrineListener` to access extension metadata during Doctrine lifecycle events:

```php
use ChamberOrchestra\MetadataBundle\EventSubscriber\AbstractDoctrineListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist)]
class TimestampableListener extends AbstractDoctrineListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        foreach ($this->getScheduledEntityInsertions($args->getEntityManager(), TimestampableConfiguration::class) as $metadataArgs) {
            $metadataArgs->extensionMetadata->setFieldValue(
                $metadataArgs->entity,
                'createdAt',
                new \DateTimeImmutable()
            );
        }
    }
}
```

## Architecture

```
MetadataSubscriber (Doctrine loadClassMetadata event)
  └── MetadataReader
        └── AbstractExtensionMetadataFactory
              ├── MappingDriverInterface[] (attribute-based mapping drivers)
              ├── ExtensionMetadataInterface (per-entity extension metadata)
              │     ├── MetadataConfigurationInterface[] (per-driver configurations)
              │     └── Embedded metadata (recursive for embeddables)
              └── PSR-6 Cache (serialized metadata storage)
```

### Key Abstractions

| Class / Interface | Role |
|---|---|
| `MappingDriverInterface` | Extension point — implement to define attribute-driven metadata |
| `AbstractMappingDriver` | Base driver with `AttributeReader` and `supports()` logic |
| `MetadataConfigurationInterface` | Stores field mappings; serializable for Doctrine cache |
| `AbstractDoctrineListener` | Base for Doctrine listeners that need extension metadata |
| `MetadataArgs` | DTO bundling EntityManager, metadata, configuration, and entity |

## Requirements

- PHP 8.5+
- Symfony 8.0
- Doctrine ORM 3.6+
- Doctrine Bundle 3.2+

## Development

```sh
composer install   # Install dependencies
composer test      # Run the test suite
```

## License

MIT
