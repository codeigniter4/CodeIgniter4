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

namespace CodeIgniter\DataCaster;

use CodeIgniter\Entity\Exceptions\CastException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class DataCasterTest extends CIUnitTestCase
{
    public function testCastInvalidMethodException(): void
    {
        $this->expectException(CastException::class);
        $this->expectExceptionMessage('The "add" is invalid cast method, valid methods are: ["get", "set"].');

        $dataCaster = new DataCaster();
        $dataCaster->castAs([], 'name', 'add');
    }
}
