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
     * @var ExtensionMetadataInterface[]
     */
    private array $loadedMetadata = [];
    private ReflectionService|null $reflectionService = null {
        get {
            return $this->reflectionService ??= new RuntimeReflectionService();
        }
    }

    /**
     * Gets the class metadata descriptor for a class.
     */
    public function getMetadataFor(EntityManagerInterface $em, ClassMetadata $metadata): ExtensionMetadataInterface
    {
        $key = \spl_object_id($em) . '#' . $metadata->getName();
        if (isset($this->loadedMetadata[$key])) {
            return $this->loadedMetadata[$key];
        }

        if ($cache = $em->getConfiguration()->getMetadataCache()) {
            // Note: cache key does not include EM identity.
            // If using multiple EntityManagers, ensure each has a separate metadata cache pool.
            $item = $cache->getItem($this->sanitize($metadata->getName()));

            if ($item->isHit()) {
                $this->wakeup($em, $metadata, $extensionMetadata = $item->get());
            } else {
                $item->set($extensionMetadata = $this->loadMetadata($em, $metadata));
                $cache->save($item);
            }
        } else {
            $extensionMetadata = $this->loadMetadata($em, $metadata);
        }

        return $this->loadedMetadata[$key] = $extensionMetadata;
    }

    /**
     * Checks whether the factory has the metadata for a class loaded already.
     */
    public function hasMetadataFor(EntityManagerInterface $em, string $className): bool
    {
        $key = \spl_object_id($em) . '#' . $className;

        return isset($this->loadedMetadata[$key]);
    }

    /**
     * Loads metadata for entity.
     */
    protected function loadMetadata(EntityManagerInterface $em, ClassMetadata $classMetadata): ExtensionMetadataInterface
    {
        $extensionMetadata = $this->newClassMetadataInstance($classMetadata);
        $this->loadEmbeddedMetadata($em, $classMetadata, $extensionMetadata);
        $this->doLoadMetadata($extensionMetadata);
        $extensionMetadata->wakeup($classMetadata, $this->reflectionService);

        return $extensionMetadata;
    }

    protected function wakeup(EntityManagerInterface $em, ClassMetadata $classMetadata, ExtensionMetadataInterface $extensionMetadata): void
    {
        $this->loadEmbeddedMetadata($em, $classMetadata, $extensionMetadata);
        $extensionMetadata->wakeup($classMetadata, $this->reflectionService);
    }

    /**
     * Actually loads the metadata from the underlying metadata.
     */
    abstract protected function doLoadMetadata(ExtensionMetadataInterface $class): void;

    /**
     * Creates a new ClassMetadata instance for the given class name.
     */
    abstract protected function newClassMetadataInstance(ClassMetadata $metadata): ExtensionMetadataInterface;

    private function loadEmbeddedMetadata(EntityManagerInterface $em, ClassMetadata $classMetadata, ExtensionMetadataInterface $extensionMetadata): void
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $classMetadata */
        foreach ($classMetadata->embeddedClasses as $fieldName => $mapping) {
            $embeddedMetadata = $em->getClassMetadata($mapping->class);
            $extensionMetadata->addEmbeddedMetadata($fieldName, $this->getMetadataFor($em, $embeddedMetadata));
        }
    }

    protected function getCacheNamespace(): string
    {
        return static::class;
    }

    private function sanitize(string $string): string
    {
        return \preg_replace('/[^a-zA-Z0-9._-]/', '_', $string . '.' . $this->getCacheNamespace()) . self::$cacheSalt;
    }
}
