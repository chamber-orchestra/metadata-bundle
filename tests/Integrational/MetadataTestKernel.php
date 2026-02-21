<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Integrational;

use ChamberOrchestra\MetadataBundle\ChamberOrchestraMetadataBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Tests\Fixtures\Mapping\TrackingMappingDriver;

final class MetadataTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new ChamberOrchestraMetadataBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->services()
            ->set(TrackingMappingDriver::class)
            ->autowire()
            ->autoconfigure()
            ->public();

        $container->extension('framework', [
            'secret' => 'test_secret',
            'test' => true,
        ]);

        $container->extension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ],
            'orm' => [
                'mappings' => [
                    'TestsFixtures' => [
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/tests/Fixtures/Doctrine',
                        'prefix' => 'Tests\\Fixtures\\Doctrine',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ]);
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__, 2);
    }
}
