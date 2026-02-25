<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\DataCollector;

use ChamberOrchestra\MetadataBundle\DataCollector\MetadataDataCollector;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MetadataDataCollectorTest extends TestCase
{
    public function testRecordsAndCollectsDriverInvocations(): void
    {
        $collector = new MetadataDataCollector();

        $collector->recordDriverInvocation('App\\Driver\\FooDriver', 'App\\Entity\\Bar', true, true);
        $collector->recordDriverInvocation('App\\Driver\\BazDriver', 'App\\Entity\\Bar', false, false);

        $collector->collect(new Request(), new Response());

        $stats = $collector->getDriverStats();

        self::assertCount(2, $stats);
        self::assertSame('App\\Driver\\FooDriver', $stats[0]['driver']);
        self::assertTrue($stats[0]['supported']);
        self::assertSame('App\\Driver\\BazDriver', $stats[1]['driver']);
        self::assertFalse($stats[1]['supported']);
    }

    public function testResetClearsData(): void
    {
        $collector = new MetadataDataCollector();

        $collector->recordDriverInvocation('App\\Driver\\FooDriver', 'App\\Entity\\Bar', true, true);
        $collector->collect(new Request(), new Response());

        self::assertCount(1, $collector->getDriverStats());

        $collector->reset();

        self::assertSame([], $collector->getDriverStats());
    }

    public function testGetName(): void
    {
        $collector = new MetadataDataCollector();

        self::assertSame('chamber_orchestra_metadata', $collector->getName());
    }

    public function testReturnsEmptyStatsBeforeCollect(): void
    {
        $collector = new MetadataDataCollector();

        self::assertSame([], $collector->getDriverStats());
    }
}
