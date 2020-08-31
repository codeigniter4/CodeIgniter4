<?php

namespace CodeIgniter;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class HomeTest extends CIUnitTestCase
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
		$this->assertInstanceOf('CodeIgniter\Test\FeatureResponse', $response);
		$this->assertTrue($response->isOK());
	}
}
