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
use CodeIgniter\Test\TestResponse;

/**
 * @internal
 *
 * @group Others
 */
final class HomeTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testPageLoadsSuccessfully(): void
    {
        $this->withRoutes([
            [
                'get',
                'home',
                '\App\Controllers\Home::index',
            ],
        ]);

        $response = $this->get('home');
        $this->assertInstanceOf(TestResponse::class, $response);
        $this->assertTrue($response->isOK());
    }
}
