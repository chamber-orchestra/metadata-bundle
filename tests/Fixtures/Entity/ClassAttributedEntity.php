<?php

declare(strict_types=1);

namespace Tests\Fixtures\Entity;

use Tests\Fixtures\Attributes\ExampleClassAttribute;

#[ExampleClassAttribute]
class ClassAttributedEntity
{
    public function __construct(public int $id = 1) {}
}
