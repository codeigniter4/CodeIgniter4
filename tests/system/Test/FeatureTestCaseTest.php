<?php

use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\FeatureResponse;

/**
 * @group DatabaseLive
 */
class FeatureTestCaseTest extends FeatureTestCase
{

	protected function setUp()
	{
		parent::setUp();

		$this->skipEvents();
		$this->clean = false;
	}

	public function testCallGet()
	{
		$this->withRoutes([
			[
				'get',
				'home',
				function () {
					return 'Hello World';
				},
			],
		]);
		$response = $this->get('home');

		// close open buffer
		ob_end_clean();

		$response->assertSee('Hello World');
		$response->assertDontSee('Again');
	}

	public function testCallSimpleGet()
	{
		$this->withRoutes([
			[
				'add',
				'home',
				function () {
					return 'Hello World';
				},
			],
		]);
		$response = $this->call('get', 'home');

		$this->assertInstanceOf(FeatureResponse::class, $response);
		$this->assertInstanceOf(\CodeIgniter\HTTP\Response::class, $response->response);
		$this->assertTrue($response->isOK());
		$this->assertEquals('Hello World', $response->response->getBody());
		$this->assertEquals(200, $response->response->getStatusCode());
	}

	public function testCallPost()
	{
		$this->withRoutes([
			[
				'post',
				'home',
				function () {
					return 'Hello World';
				},
			],
		]);
		$response = $this->post('home');

		$response->assertSee('Hello World');
	}

	public function testCallPut()
	{
		$this->withRoutes([
			[
				'put',
				'home',
				function () {
					return 'Hello World';
				},
			],
		]);
		$response = $this->put('home');

		$response->assertSee('Hello World');
	}

	public function testCallPatch()
	{
		$this->withRoutes([
			[
				'patch',
				'home',
				function () {
					return 'Hello World';
				},
			],
		]);
		$response = $this->patch('home');

		$response->assertSee('Hello World');
	}

	public function testCallOptions()
	{
		$this->withRoutes([
			[
				'options',
				'home',
				function () {
					return 'Hello World';
				},
			],
		]);
		$response = $this->options('home');

		$response->assertSee('Hello World');
	}

	public function testCallDelete()
	{
		$this->withRoutes([
			[
				'delete',
				'home',
				function () {
					return 'Hello World';
				},
			],
		]);
		$response = $this->delete('home');

		$response->assertSee('Hello World');
	}

	public function testSession()
	{
		$response = $this->withSession([
			'fruit'    => 'apple',
			'greeting' => 'hello',
		])->get('home');

		$response->assertSessionHas('fruit', 'apple');
		$response->assertSessionMissing('popcorn');
	}

}
