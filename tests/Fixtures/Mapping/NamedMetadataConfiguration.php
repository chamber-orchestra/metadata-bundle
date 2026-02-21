<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Mapping;

use ChamberOrchestra\MetadataBundle\Mapping\ORM\EntityNameAwareInterface;

class NamedMetadataConfiguration extends TestMetadataConfiguration implements EntityNameAwareInterface
{
    public function __construct(private readonly string $entityName)
    {
    }

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
