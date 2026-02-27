<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\Mapping;

use ChamberOrchestra\MetadataBundle\Mapping\ORM\ExtensionMetadataFactory;
use ChamberOrchestra\MetadataBundle\Mapping\ORM\MetadataConfigurationInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\MappingException;

class MetadataReader
{
    /**
     * @var \WeakMap<EntityManagerInterface, array<string, ExtensionMetadataInterface>>
     */
    private \WeakMap $configurations;

    public function __construct(private readonly ExtensionMetadataFactory $factory)
    {
        $this->configurations = new \WeakMap();
    }

    /**
     * @param ClassMetadata<object> $metadata
     */
    public function loadExtensionMetadata(EntityManagerInterface $em, ClassMetadata $metadata): void
    {
        $emConfigs = $this->configurations[$em] ??= [];
        $className = $metadata->getName();
        if (isset($emConfigs[$className])) {
            return;
        }

        $config = $this->factory->getMetadataFor($em, $metadata);
        $emConfigs[$className] = $config;
        $this->configurations[$em] = $emConfigs;
    }

    /**
     * Returns extension metadata for the given entity class, loading it on first access.
     *
     * Subsequent calls for the same EM and class return the cached instance.
     */
    public function getExtensionMetadata(EntityManagerInterface $em, string $class): ExtensionMetadataInterface
    {
        $emConfigs = $this->configurations[$em] ??= [];
        if (isset($emConfigs[$class])) {
            return $emConfigs[$class];
        }

        // Resolve canonical class name (handles proxies, aliases, etc.)
        $metadata = $em->getClassMetadata($class);
        $canonicalClass = $metadata->getName();

        // If the input class is already canonical, avoid duplicate cache entry
        if ($class === $canonicalClass) {
            $this->loadExtensionMetadata($em, $metadata);

            return $this->configurations[$em][$class];
        }

        // For aliases/proxies, ensure canonical key exists, then alias it
        $emConfigs = $this->configurations[$em];
        if (!isset($emConfigs[$canonicalClass])) {
            $this->loadExtensionMetadata($em, $metadata);
            $emConfigs = $this->configurations[$em];
        }

        $emConfigs[$class] = $emConfigs[$canonicalClass];
        $this->configurations[$em] = $emConfigs;

        return $emConfigs[$class];
    }

    /**
     * @throws MappingException
     */
    public function getExtensionConfiguration(EntityManagerInterface $em, string $entityClass, string $configurationClass): ?MetadataConfigurationInterface
    {
        $metadata = $this->getExtensionMetadata($em, $entityClass);

        return $metadata->getConfiguration($configurationClass);
    }
}
