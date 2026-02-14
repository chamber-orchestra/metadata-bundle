[![PHP Composer](https://github.com/chamber-orchestra/metadata-bundle/actions/workflows/php.yml/badge.svg)](https://github.com/chamber-orchestra/metadata-bundle/actions/workflows/php.yml)

# Doctrine ORM Extension Metadata Bundle for Symfony

A Symfony bundle for extending Doctrine ORM entities with custom attribute-driven metadata. It provides a cacheable mapping layer, embedded entity support, and event-driven architecture for building reusable Doctrine extensions.

## Features

- **Attribute-based mapping** — define custom metadata using native PHP attributes on entity classes and properties
- **PSR-6 metadata caching** — multi-level cache (PSR-6 + in-memory) with serialization support for production performance
- **Embedded entity support** — automatic metadata resolution for Doctrine embeddables with lazy field initialization
- **Autoconfigured mapping drivers** — implement `MappingDriverInterface` and drivers are auto-tagged via Symfony DI
- **Doctrine event integration** — hooks into `loadClassMetadata` to load extension metadata alongside Doctrine's own metadata
- **Multiple EntityManager support** — cache isolation per EntityManager via `spl_object_id` scoping

## How It Works

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

## Requirements

- PHP 8.5+
- Symfony 8.0
- Doctrine ORM 3.6+
- Doctrine Bundle 3.2+

## Installation

```sh
composer require chamber-orchestra/metadata-bundle
```

## Development

Install dependencies:

```sh
composer install
```

Run the test suite:

```sh
composer test
```

## License

MIT
