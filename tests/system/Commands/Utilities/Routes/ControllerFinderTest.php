<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Utilities\Routes;

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Controllers\Hello;

/**
 * @internal
 *
 * @group Others
 */
final class ControllerFinderTest extends CIUnitTestCase
{
    public function testFind(): void
    {
        $namespace = 'Tests\Support\Controllers';
        $finder    = new ControllerFinder($namespace);

        $controllers = $finder->find();

        $this->assertCount(4, $controllers);
        $this->assertSame(Hello::class, $controllers[0]);
    }
}
