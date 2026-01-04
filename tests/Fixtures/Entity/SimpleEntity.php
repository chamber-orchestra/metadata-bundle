<?php

declare(strict_types=1);

namespace Tests\Fixtures\Entity;

class SimpleEntity
{
    private string $name = '';

    public function getName(): string
    {
        return $this->name;
    }
}
