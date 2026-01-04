<?php

declare(strict_types=1);

namespace Tests\Fixtures\Mapping;

class NamedMetadataConfiguration extends TestMetadataConfiguration
{
    public function __construct(private readonly string $entityName)
    {
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }
}
