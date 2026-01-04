<?php

declare(strict_types=1);

namespace Tests\Fixtures\Entity;

use Tests\Fixtures\Attributes\ExamplePropertyAttribute;

class PropertyAttributedEntity
{
    #[ExamplePropertyAttribute]
    public int $id = 1;
}
