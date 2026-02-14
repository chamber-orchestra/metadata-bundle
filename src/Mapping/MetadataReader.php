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
     * @var ExtensionMetadataInterface[]
     */
    private array $configurations = [];

    public function __construct(private readonly ExtensionMetadataFactory $factory)
    {
    }

    public function loadExtensionMetadata(EntityManagerInterface $em, ClassMetadata $metadata): void
    {
        $key = \spl_object_id($em) . '#' . $metadata->getName();
        if (isset($this->configurations[$key])) {
            return;
        }

        $config = $this->factory->getMetadataFor($em, $metadata);
        $this->configurations[$key] = $config;
    }

    /**
     * Returns extension metadata for the given entity class, loading it on first access.
     *
     * Subsequent calls for the same EM and class return the cached instance.
     */
    public function getExtensionMetadata(EntityManagerInterface $em, string $class): ExtensionMetadataInterface
    {
        $key = \spl_object_id($em) . '#' . $class;
        if (isset($this->configurations[$key])) {
            return $this->configurations[$key];
        }

        // Resolve canonical class name (handles proxies, aliases, etc.)
        $metadata = $em->getClassMetadata($class);
        $canonicalClass = $metadata->getName();

        // If the input class is already canonical, avoid duplicate cache entry
        if ($class === $canonicalClass) {
            $this->loadExtensionMetadata($em, $metadata);

            return $this->configurations[$key];
        }

        // For aliases/proxies, ensure canonical key exists, then alias it
        $resolvedKey = \spl_object_id($em) . '#' . $canonicalClass;
        if (!isset($this->configurations[$resolvedKey])) {
            $this->loadExtensionMetadata($em, $metadata);
        }

        return $this->configurations[$key] = $this->configurations[$resolvedKey];
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
