<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\MetadataBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class MetadataDataCollector extends DataCollector
{
    /**
     * @var list<array{driver: string, entity: string, supported: bool, loaded: bool}>
     */
    private array $invocations = [];

    public function recordDriverInvocation(string $driverClass, string $entityClass, bool $supported, bool $loaded): void
    {
        $this->invocations[] = [
            'driver' => $driverClass,
            'entity' => $entityClass,
            'supported' => $supported,
            'loaded' => $loaded,
        ];
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $this->data = [
            'invocations' => $this->invocations,
        ];
    }

    /**
     * @return list<array{driver: string, entity: string, supported: bool, loaded: bool}>
     */
    public function getDriverStats(): array
    {
        /** @var list<array{driver: string, entity: string, supported: bool, loaded: bool}> $stats */
        $stats = $this->data['invocations'] ?? [];

        return $stats;
    }

    public function getName(): string
    {
        return 'chamber_orchestra_metadata';
    }

    public function reset(): void
    {
        $this->data = [];
        $this->invocations = [];
    }
}
