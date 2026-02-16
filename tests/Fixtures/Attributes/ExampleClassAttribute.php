<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use Doctrine\ORM\Mapping\MappingAttribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class ExampleClassAttribute implements MappingAttribute
{
}
