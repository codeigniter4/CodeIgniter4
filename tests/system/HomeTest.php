<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class HomeTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testPageLoadsSuccessfully()
    {
        $this->withRoutes([
            [
                'get',
                'home',
                '\App\Controllers\Home::index',
            ],
        ]);

        $response = $this->get('home');
        $this->assertInstanceOf('CodeIgniter\Test\TestResponse', $response);
        $this->assertTrue($response->isOK());
    }
}
