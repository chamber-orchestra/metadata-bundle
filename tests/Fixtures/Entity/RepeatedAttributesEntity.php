<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Entity;

use Tests\Fixtures\Attributes\ExampleClassAttribute;
use Tests\Fixtures\Attributes\RepeatableClassAttribute;

#[ExampleClassAttribute]
#[RepeatableClassAttribute('first')]
#[RepeatableClassAttribute('second')]
class RepeatedAttributesEntity
{
}
