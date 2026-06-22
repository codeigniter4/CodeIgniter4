<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Entity\Cast;

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

/**
 * @internal
 */
#[Group('Others')]
final class ArrayCastTest extends CIUnitTestCase
{
    public function testGetPreventsObjectInjection(): void
    {
        $payload = serialize([new stdClass()]);

        $result = ArrayCast::get($payload);

        $this->assertInstanceOf('__PHP_Incomplete_Class', $result[0]);
    }
}
