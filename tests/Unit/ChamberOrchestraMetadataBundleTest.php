<?php

declare(strict_types=1);

namespace Tests\Unit;

use ChamberOrchestra\MetadataBundle\ChamberOrchestraMetadataBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ChamberOrchestraMetadataBundleTest extends TestCase
{
    public function testBundleExtendsSymfonyBundle(): void
    {
        $bundle = new ChamberOrchestraMetadataBundle();

        self::assertInstanceOf(Bundle::class, $bundle);
    }
}
