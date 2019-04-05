<?php namespace Test;

use CodeIgniter\Test\FeatureTestCase;

class HomeTest extends FeatureTestCase
{
	public function testHomePage()
	{
		$result = $this->skipEvents()->call('get', '/');

		$result->assertOk();

		$result->assertStatus(200);

		$result->assertSee('CodeIgniter');
		$result->assertDontSee('Symfony');
	}

	public function testJson()
	{
		$result = $this->get('api');

		$json = $result->getJSON();

		$result->assertJSONFragment(['foo' => 'bar']);
		$result->assertJSONExact([
			'foo' => 'bar',
			'bar' => 'none',
		]);
	}
}
