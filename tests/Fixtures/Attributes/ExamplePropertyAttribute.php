<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use Doctrine\ORM\Mapping\MappingAttribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ExamplePropertyAttribute implements MappingAttribute
{
}
