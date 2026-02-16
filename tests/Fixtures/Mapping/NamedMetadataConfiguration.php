<?php

declare(strict_types=1);

namespace Tests\Fixtures\Mapping;

use ChamberOrchestra\MetadataBundle\Mapping\ORM\EntityNameAwareInterface;

class NamedMetadataConfiguration extends TestMetadataConfiguration implements EntityNameAwareInterface
{
    public function __construct(private readonly string $entityName) {}

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function __serialize(): array
    {
        return \array_merge(parent::__serialize(), ['entityName' => $this->entityName]);
    }

    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);
        $this->entityName = $data['entityName'];
    }
}
