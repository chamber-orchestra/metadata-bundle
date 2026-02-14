[![PHP Composer](https://github.com/chamber-orchestra/metadata-bundle/actions/workflows/php.yml/badge.svg)](https://github.com/chamber-orchestra/metadata-bundle/actions/workflows/php.yml)

# Chamber Orchestra Metadata Bundle

A Symfony bundle that augments Doctrine ORM entities with extension metadata. It provides a metadata factory/reader, mapping drivers, and subscriber hooks to load custom mapping configuration during Doctrine metadata loading.

## Dependencies

Core requirements (see `composer.json`):

- PHP 8.5
- Symfony 8.0 components: `dependency-injection`, `config`, `framework-bundle`, `runtime`, `options-resolver`
- Doctrine ORM 3.x and Doctrine Bundle 3.2

Development:

- PHPUnit 12.5
- Symfony Test Pack

Install dependencies with:

```sh
composer install
```

## Running Tests

Run the full test suite:

```sh
bin/phpunit
```

Run a focused subset by class or method:

```sh
bin/phpunit --filter SomeTest
```
