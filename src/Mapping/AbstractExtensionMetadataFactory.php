<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\Mapping;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata as OrmClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ReflectionService;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;

abstract class AbstractExtensionMetadataFactory
{
    /**
     * Cache key suffix to namespace extension metadata entries.
     *
     * The '$' prefix prevents collision with user-defined class names,
     * as '$' is not a valid character in PHP identifiers.
     */
    private static string $cacheSalt = '$EXTENSIONMETADATA';
    /**
     * @var \WeakMap<EntityManagerInterface, array<string, ExtensionMetadataInterface>>
     */
    private \WeakMap $loadedMetadata;
    private ?ReflectionService $reflectionService = null;

    /**
     * Gets the class metadata descriptor for a class.
     *
     * @param ClassMetadata<object> $metadata
     */
    public function getMetadataFor(EntityManagerInterface $em, ClassMetadata $metadata): ExtensionMetadataInterface
    {
        $emMetadata = $this->getLoadedMetadata()[$em] ?? [];
        $className = $metadata->getName();
        if (isset($emMetadata[$className])) {
            return $emMetadata[$className];
        }

        if ($cache = $em->getConfiguration()->getMetadataCache()) {
            // Note: cache key does not include EM identity.
            // If using multiple EntityManagers, ensure each has a separate metadata cache pool.
            $item = $cache->getItem($this->sanitize($className));

            if ($item->isHit()) {
                $extensionMetadata = $item->get();
                \assert($extensionMetadata instanceof ExtensionMetadataInterface);
                $this->wakeup($em, $metadata, $extensionMetadata);
            } else {
                $extensionMetadata = $this->loadMetadata($em, $metadata);
                $item->set($extensionMetadata);
                $cache->save($item);
            }
        } else {
            $extensionMetadata = $this->loadMetadata($em, $metadata);
        }

        $loadedMetadata = $this->getLoadedMetadata();
        $emMetadata = $loadedMetadata[$em] ?? [];
        $emMetadata[$className] = $extensionMetadata;
        $loadedMetadata[$em] = $emMetadata;

        return $extensionMetadata;
    }

    /**
     * Checks whether the factory has the metadata for a class loaded already.
     */
    public function hasMetadataFor(EntityManagerInterface $em, string $className): bool
    {
        $emMetadata = $this->getLoadedMetadata()[$em] ?? [];

        return isset($emMetadata[$className]);
    }

    /**
     * Loads metadata for entity.
     *
     * @param ClassMetadata<object> $classMetadata
     */
    protected function loadMetadata(EntityManagerInterface $em, ClassMetadata $classMetadata): ExtensionMetadataInterface
    {
        $extensionMetadata = $this->newClassMetadataInstance($classMetadata);
        $this->loadEmbeddedMetadata($em, $classMetadata, $extensionMetadata);
        $this->doLoadMetadata($extensionMetadata);
        $extensionMetadata->wakeup($classMetadata, $this->getReflectionService());

        return $extensionMetadata;
    }

    /**
     * @param ClassMetadata<object> $classMetadata
     */
    protected function wakeup(EntityManagerInterface $em, ClassMetadata $classMetadata, ExtensionMetadataInterface $extensionMetadata): void
    {
        $this->loadEmbeddedMetadata($em, $classMetadata, $extensionMetadata);
        $extensionMetadata->wakeup($classMetadata, $this->getReflectionService());
    }

    /**
     * Actually loads the metadata from the underlying metadata.
     */
    abstract protected function doLoadMetadata(ExtensionMetadataInterface $class): void;

    /**
     * Creates a new ClassMetadata instance for the given class name.
     *
     * @param ClassMetadata<object> $metadata
     */
    abstract protected function newClassMetadataInstance(ClassMetadata $metadata): ExtensionMetadataInterface;

    /**
     * @param ClassMetadata<object> $classMetadata
     */
    private function loadEmbeddedMetadata(EntityManagerInterface $em, ClassMetadata $classMetadata, ExtensionMetadataInterface $extensionMetadata): void
    {
        \assert($classMetadata instanceof OrmClassMetadata);
        foreach ($classMetadata->embeddedClasses as $fieldName => $mapping) {
            $embeddedMetadata = $em->getClassMetadata($mapping->class);
            $extensionMetadata->addEmbeddedMetadata($fieldName, $this->getMetadataFor($em, $embeddedMetadata));
        }
    }

    /**
     * @return \WeakMap<EntityManagerInterface, array<string, ExtensionMetadataInterface>>
     */
    private function getLoadedMetadata(): \WeakMap
    {
        return $this->loadedMetadata ??= new \WeakMap();
    }

    private function getReflectionService(): ReflectionService
    {
        return $this->reflectionService ??= new RuntimeReflectionService();
    }

    protected function getCacheNamespace(): string
    {
        return static::class;
    }

    private function sanitize(string $string): string
    {
        $result = \preg_replace('/[^a-zA-Z0-9._-]/', '_', $string.'.'.$this->getCacheNamespace());
        \assert(\is_string($result));

        return $result.self::$cacheSalt;
    }
}
