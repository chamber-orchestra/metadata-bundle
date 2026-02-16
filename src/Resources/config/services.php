<?php

declare(strict_types=1);

use ChamberOrchestra\MetadataBundle\EventSubscriber\MetadataSubscriber;
use ChamberOrchestra\MetadataBundle\Mapping\MetadataReader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services->load('ChamberOrchestra\\MetadataBundle\\', '../../*')
        ->exclude([
            '../../ChamberOrchestraMetadataBundle.php',
            '../../DependencyInjection',
            '../../Resources',
            '../../Exception',
            '../../Helper',
            '../../Mapping/AbstractExtensionMetadata.php',
            '../../Mapping/ORM/AbstractMetadataConfiguration.php',
            '../../Mapping/ORM/ExtensionMetadata.php',
        ]);

    $services->set(MetadataReader::class)
        ->lazy();

    $services->set(MetadataSubscriber::class);
};
