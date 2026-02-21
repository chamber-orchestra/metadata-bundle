<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Entity;

class EntityWithEmbedded
{
    private EmbeddedValue $embedded;
    private string $name = '';

    public function __construct()
    {
        $this->embedded = new EmbeddedValue();
    }

    public function getEmbedded(): EmbeddedValue
    {
        return $this->embedded;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
