<?php

declare(strict_types=1);

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
