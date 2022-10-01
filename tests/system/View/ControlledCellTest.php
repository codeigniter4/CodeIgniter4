<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\View;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ControlledCellTest extends CIUnitTestCase
{
    public function testCellRendersDefaultValues()
    {
        $result = view_cell('Tests\Support\View\Cells\GreetingCell');

        $this->assertStringContainsString('Hello World', (string)$result);
    }
}
