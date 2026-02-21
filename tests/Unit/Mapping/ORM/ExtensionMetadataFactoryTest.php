<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Mapping\ORM;

use ChamberOrchestra\MetadataBundle\Mapping\Driver\MappingDriverInterface;
use ChamberOrchestra\MetadataBundle\Mapping\ORM\ExtensionMetadataFactory;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Entity\SimpleEntity;

final class ExtensionMetadataFactoryTest extends TestCase
{
    public function testLoadsMetadataFromSupportedDrivers(): void
    {
        $supportedDriver = $this->createMock(MappingDriverInterface::class);
        $supportedDriver->expects(self::once())
            ->method('supports')
            ->willReturn(true);
        $supportedDriver->expects(self::once())
            ->method('loadMetadataForClass');

        $unsupportedDriver = $this->createMock(MappingDriverInterface::class);
        $unsupportedDriver->expects(self::once())
            ->method('supports')
            ->willReturn(false);
        $unsupportedDriver->expects(self::never())
            ->method('loadMetadataForClass');

        $factory = new ExtensionMetadataFactory([$supportedDriver, $unsupportedDriver]);

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getConfiguration')->willReturn(new Configuration());

        $metadata = new ClassMetadata(SimpleEntity::class);

        $factory->getMetadataFor($entityManager, $metadata);
    }
}
