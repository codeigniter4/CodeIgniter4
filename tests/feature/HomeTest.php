<?php

use CodeIgniter\Test\FeatureTestCase;

/**
 * @group DatabaseLive
 */
class HomeTest extends FeatureTestCase
{
	public function testCanLoadPage()
	{
		$response = $this->call('post', site_url().'?foo=bar&bar=baz', ['xxx' => 'yyy']);

		$this->assertInstanceOf(\CodeIgniter\Test\FeatureResponse::class, $response);
	}

}
