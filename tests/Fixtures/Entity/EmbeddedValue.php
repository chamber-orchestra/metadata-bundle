<?php

declare(strict_types=1);

namespace Tests\Fixtures\Entity;

class EmbeddedValue
{
    private string $value = '';

    public function getValue(): string
    {
        return $this->value;
    }
}
