<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Command;

use ChamberOrchestra\MetadataBundle\Command\MetadataDumpCommand;
use ChamberOrchestra\MetadataBundle\Mapping\MetadataReader;
use ChamberOrchestra\MetadataBundle\Mapping\ORM\ExtensionMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Fixtures\Entity\SimpleEntity;
use Tests\Fixtures\Mapping\TestMetadataConfiguration;

final class MetadataDumpCommandTest extends TestCase
{
    public function testDumpsMetadataForEntity(): void
    {
        $configuration = new TestMetadataConfiguration();
        $configuration->mapField('name');

        $extensionMetadata = new ExtensionMetadata(new ClassMetadata(SimpleEntity::class));
        $extensionMetadata->addConfiguration($configuration);

        $reader = $this->createStub(MetadataReader::class);
        $reader->method('getExtensionMetadata')->willReturn($extensionMetadata);

        $em = $this->createStub(EntityManagerInterface::class);

        $command = new MetadataDumpCommand($reader, $em);
        $tester = new CommandTester($command);

        $tester->execute(['entity-class' => SimpleEntity::class]);

        $output = $tester->getDisplay();
        self::assertStringContainsString(SimpleEntity::class, $output);
        self::assertStringContainsString(TestMetadataConfiguration::class, $output);
        self::assertStringContainsString('name', $output);
        self::assertSame(0, $tester->getStatusCode());
    }

    public function testReportsNoConfigurations(): void
    {
        $extensionMetadata = new ExtensionMetadata(new ClassMetadata(SimpleEntity::class));

        $reader = $this->createStub(MetadataReader::class);
        $reader->method('getExtensionMetadata')->willReturn($extensionMetadata);

        $em = $this->createStub(EntityManagerInterface::class);

        $command = new MetadataDumpCommand($reader, $em);
        $tester = new CommandTester($command);

        $tester->execute(['entity-class' => SimpleEntity::class]);

        $output = $tester->getDisplay();
        self::assertStringContainsString('No metadata configurations found', $output);
        self::assertSame(0, $tester->getStatusCode());
    }

    public function testFailsForNonexistentClass(): void
    {
        $reader = $this->createStub(MetadataReader::class);
        $em = $this->createStub(EntityManagerInterface::class);

        $command = new MetadataDumpCommand($reader, $em);
        $tester = new CommandTester($command);

        $tester->execute(['entity-class' => 'NonExistent\\Class\\Name']);

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('does not exist', $tester->getDisplay());
    }
}
