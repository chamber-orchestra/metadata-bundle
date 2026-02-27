<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\Command;

use ChamberOrchestra\MetadataBundle\Mapping\MetadataReader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'chamber-orchestra:metadata:dump',
    description: 'Dump extension metadata for a given entity class',
)]
class MetadataDumpCommand extends Command
{
    public function __construct(
        private readonly MetadataReader $metadataReader,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('entity-class', InputArgument::REQUIRED, 'The fully-qualified entity class name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $entityClass */
        $entityClass = $input->getArgument('entity-class');

        if (!\class_exists($entityClass)) {
            $io->error(\sprintf('Class "%s" does not exist.', $entityClass));

            return Command::FAILURE;
        }

        $metadata = $this->metadataReader->getExtensionMetadata($this->entityManager, $entityClass);

        $io->title(\sprintf('Extension Metadata for %s', $entityClass));

        $configurations = $metadata->getConfigurations();

        if ([] === $configurations) {
            $io->info('No metadata configurations found for this entity.');

            return Command::SUCCESS;
        }

        foreach ($configurations as $configClass => $configuration) {
            $io->section($configClass);

            $mappings = $configuration->getMappings();
            if ([] === $mappings) {
                $io->text('No field mappings.');
                continue;
            }

            $rows = [];
            foreach ($mappings as $fieldName => $mapping) {
                $rows[] = [$fieldName, \json_encode($mapping, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT)];
            }

            $io->table(['Field', 'Mapping'], $rows);
        }

        $embeddedMetadata = $metadata->getEmbeddedMetadata();
        if ([] !== $embeddedMetadata) {
            $io->section('Embedded Metadata');
            foreach ($embeddedMetadata as $fieldName => $embeddedMeta) {
                $io->text(\sprintf('  %s -> %s', $fieldName, $embeddedMeta->getName()));
                foreach ($embeddedMeta->getConfigurations() as $configClass => $configuration) {
                    $io->text(\sprintf('    Configuration: %s (%d fields)', $configClass, \count($configuration->getFieldNames())));
                }
            }
        }

        return Command::SUCCESS;
    }
}
