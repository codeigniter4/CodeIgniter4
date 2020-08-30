<?php

namespace CodeIgniter;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class HomeTest extends CIUnitTestCase
{
	use FeatureTestTrait;

	public function testPageLoadsSuccessfully()
	{
		$response = $this->get('/');
		$this->assertTrue($response->isOK());
	}
}
