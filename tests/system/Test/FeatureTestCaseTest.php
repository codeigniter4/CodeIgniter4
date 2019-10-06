<?php

use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\FeatureResponse;

/**
 * @group                       DatabaseLive
 * @runTestsInSeparateProcesses
 * @preserveGlobalState         disabled
 */
class FeatureTestCaseTest extends FeatureTestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		$this->skipEvents();
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
					return 'Hello Earth';
				},
			],
		]);
		$response = $this->call('get', 'home');

		$this->assertInstanceOf(FeatureResponse::class, $response);
		$this->assertInstanceOf(\CodeIgniter\HTTP\Response::class, $response->response);
		$this->assertTrue($response->isOK());
		$this->assertEquals('Hello Earth', $response->response->getBody());
		$this->assertEquals(200, $response->response->getStatusCode());
	}

	public function testCallPost()
	{
		$this->withRoutes([
			[
				'post',
				'home',
				function () {
					return 'Hello Mars';
				},
			],
		]);
		$response = $this->post('home');

		$response->assertSee('Hello Mars');
	}

	public function testCallPut()
	{
		$this->withRoutes([
			[
				'put',
				'home',
				function () {
					return 'Hello Pluto';
				},
			],
		]);
		$response = $this->put('home');

		$response->assertSee('Hello Pluto');
	}

	public function testCallPatch()
	{
		$this->withRoutes([
			[
				'patch',
				'home',
				function () {
					return 'Hello Jupiter';
				},
			],
		]);
		$response = $this->patch('home');

		$response->assertSee('Hello Jupiter');
	}

	public function testCallOptions()
	{
		$this->withRoutes([
			[
				'options',
				'home',
				function () {
					return 'Hello George';
				},
			],
		]);
		$response = $this->options('home');

		$response->assertSee('Hello George');
	}

	public function testCallDelete()
	{
		$this->withRoutes([
			[
				'delete',
				'home',
				function () {
					return 'Hello Wonka';
				},
			],
		]);
		$response = $this->delete('home');

		$response->assertSee('Hello Wonka');
	}

	public function testSession()
	{
		$response = $this->withRoutes([
			[
				'get',
				'home',
				function () {
					return 'Home';
				},
			],
		])->withSession([
			  'fruit'    => 'apple',
			  'greeting' => 'hello',
		  ])->get('home');

		$response->assertSessionHas('fruit', 'apple');
		$response->assertSessionMissing('popcorn');
	}

	public function testReturns()
	{
		$this->withRoutes([
			[
				'get',
				'home',
				'Tests\Support\Controllers\Popcorn::index',
			],
		]);
		$response = $this->get('home');
		$response->assertSee('Hi');
	}

	public function testIgnores()
	{
		$this->withRoutes([
			[
				'get',
				'home',
				'Tests\Support\Controllers\Popcorn::cat',
			],
		]);
		$response = $this->get('home');
		$response->assertEmpty($response->response->getBody());
	}

	public function testEchoes()
	{
		$this->withRoutes([
			[
				'get',
				'home',
				'Tests\Support\Controllers\Popcorn::canyon',
			],
		]);
		$response = $this->get('home');
		$response->assertSee('Hello-o-o');
	}

}
