# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A Symfony bundle (`chamber-orchestra/metadata-bundle`) that augments Doctrine ORM entities with extension metadata. It hooks into Doctrine's `loadClassMetadata` event to run custom mapping drivers that read PHP 8.5 attributes and attach additional `MetadataConfiguration` objects to entities (including embedded classes).

## Commands

```sh
composer install                    # Install dependencies
bin/phpunit                         # Run full test suite
bin/phpunit --filter SomeTest       # Run specific test class/method
composer run-script test            # Alias for bin/phpunit
```

## Architecture

### Data flow

1. Doctrine fires `loadClassMetadata` → `MetadataSubscriber` receives it
2. `MetadataSubscriber` delegates to `MetadataReader::loadExtensionMetadata()`
3. `MetadataReader` calls `ExtensionMetadataFactory::getMetadataFor()` (with Doctrine metadata cache integration)
4. The factory creates an `ExtensionMetadata` wrapper, loads embedded class metadata recursively, then iterates all tagged `MappingDriverInterface` implementations — each driver that `supports()` the entity calls `loadMetadataForClass()` to populate `MetadataConfiguration` objects
5. After driver loading, `wakeup()` resolves reflection fields (including `ReflectionEmbeddedProperty` for embedded values)

### Key abstractions

- **`MappingDriverInterface`** — extension point for consumers. Implement this to define custom attribute-driven metadata. Auto-tagged via `chamber_orchestra_metadata.mapping.driver` (autoconfiguration in the DI extension).
- **`AbstractMappingDriver`** — base driver using `AttributeReader`. Override `getClassAnnotation()` / `getPropertyAnnotation()` to control `supports()` logic; override `supportsEmbedded()` to opt into embedded class scanning.
- **`MetadataConfigurationInterface` / `AbstractMetadataConfiguration`** — stores field mappings produced by drivers. Serializable for Doctrine metadata cache. Each entity's `ExtensionMetadata` holds a map of configuration objects keyed by class name.
- **`AbstractDoctrineListener`** + `MetadataConfigurationTrait` — base for Doctrine event listeners that need to access extension metadata. Provides `getScheduledEntity{Insertions,Updates,Deletions}()` helpers that filter UoW by configuration class and return `MetadataArgs` value objects.
- **`MetadataArgs`** — DTO bundling `EntityManager`, `ExtensionMetadata`, `MetadataConfiguration`, and the entity. Uses PHP 8.5 property hooks for lazy `classMetadata` resolution.

### Namespace → `src/` layout

- `Mapping/` — core: `MetadataReader`, `ExtensionMetadataInterface`, `AbstractExtensionMetadata`, `AbstractExtensionMetadataFactory`
- `Mapping/ORM/` — Doctrine ORM specifics: `ExtensionMetadataFactory`, `ExtensionMetadata`, `AbstractMetadataConfiguration`
- `Mapping/Driver/` — driver interface and abstract base
- `Reader/` — `AttributeReader` (thin wrapper around Doctrine's `AttributeReader`)
- `EventSubscriber/` — `MetadataSubscriber`, `AbstractDoctrineListener`, `MetadataConfigurationTrait`
- `DependencyInjection/` — bundle extension, service loading, driver autoconfiguration
- `Helper/` — `MetadataArgs` DTO

### Service Configuration

Services are autowired and autoconfigured via `src/Resources/config/services.php`. The config excludes `DependencyInjection`, `Resources`, `ExceptionInterface`, `Helper`, and several non-service classes from auto-loading. `MetadataReader` is registered as lazy. `MappingDriverInterface` implementations are autoconfigured with the `chamber_orchestra_metadata.mapping.driver` tag.

## Testing

- Unit tests in `tests/Unit/` mirror the `src/` directory structure
- Integration tests in `tests/Integrational/` use `TestKernel` which boots a minimal Symfony app with FrameworkBundle, DoctrineBundle, and ChamberOrchestraMetadataBundle
- Test fixtures (entities, attributes, mapping drivers) live in `tests/Fixtures/`

## Code Style

- PHP 8.5 with `declare(strict_types=1)` in every file
- PSR-4 autoloading: `ChamberOrchestra\MetadataBundle\` → `src/`, `Tests\` → `tests/`
- PSR-12 formatting, 4-space indentation
- One class/interface/trait per file matching the filename
- CI runs on PHP 8.5 via GitHub Actions (`.github/workflows/php.yml`)

## Dependencies

- Requires PHP 8.5, Symfony 8.0 components, Doctrine ORM 3.6, Doctrine Bundle 3.2
- Part of the `chamber-orchestra` bundle ecosystem (sibling: `chamber-orchestra/form-bundle`)
