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
    private static array $configurations = [];

    public function __construct(private readonly ExtensionMetadataFactory $factory)
    {
    }

    public function loadExtensionMetadata(EntityManagerInterface $em, ClassMetadata $metadata): void
    {
        if (isset(self::$configurations[$name = $metadata->getName()])) {
            return;
        }

        $config = $this->factory->getMetadataFor($em, $metadata);
        self::$configurations[$name] = $config;
    }

    public function getExtensionMetadata(EntityManagerInterface $em, string $class): ExtensionMetadataInterface
    {
        $metadata = $em->getClassMetadata($class);
        if (!isset(self::$configurations[$name = $metadata->getName()])) {
            $this->loadExtensionMetadata($em, $metadata);
        }

        return self::$configurations[$name];
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
