<?php

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
