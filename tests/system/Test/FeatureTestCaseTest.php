<?php

use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\FeatureResponse;

	define('TESTER', 'testing.com');

/**
 * @group DatabaseLive
 */
class FeatureTestCaseTest extends FeatureTestCase
{

	protected function setUp()
	{
		parent::setUp();

		$this->skipEvents();
		$this->clean          = false;
		$_SERVER['HTTP_HOST'] = TESTER;
	}

	public function testCallGet()
	{
		$this->withRoutes([
			[
				'get',
				'home',
				function () {
					return 'My World';
				},
				['hostname' => TESTER],
			],
		]);
					echo var_dump($this->routes);
		$response = $this->call('get', '/home');
		echo var_dump($response->response->getBody());
		$response->assertSee('My World');
		$response->assertDontSee('Again');
	}

	//  public function testCallSimpleGet()
	//  {
	//      $this->withRoutes([
	//          [
	//              'add',
	//              'home',
	//              function () {
	//                  return 'Hello World';
	//              },
	//              ['hostname'=>TESTER],
	//          ],
	//      ]);
	//      $response = $this->call('get', 'home');
	//
	//      $this->assertInstanceOf(FeatureResponse::class, $response);
	//      $this->assertInstanceOf(\CodeIgniter\HTTP\Response::class, $response->response);
	//      $this->assertTrue($response->isOK());
	//      $this->assertEquals('Hello World', $response->response->getBody());
	//      $this->assertEquals(200, $response->response->getStatusCode());
	//  }
	//
	//  public function testCallPost()
	//  {
	//      $this->withRoutes([
	//          [
	//              'post',
	//              'home3',
	//              function () {
	//                  return 'Hello World';
	//              },
	//          ],
	//      ]);
	//              echo var_dump($this->routes);
	//      $response = $this->post('home3');
	//
	//      $response->assertSee('Hello World');
	//  }
	//
	//  public function testCallPut()
	//  {
	//      $this->withRoutes([
	//          [
	//              'put',
	//              'home4',
	//              function () {
	//                  return 'Hello World';
	//              },
	//          ],
	//      ]);
	//      $response = $this->put('home4');
	//
	//      $response->assertSee('Hello World');
	//  }
	//
	//  public function testCallPatch()
	//  {
	//      $this->withRoutes([
	//          [
	//              'patch',
	//              'home5',
	//              function () {
	//                  return 'Hello World';
	//              },
	//          ],
	//      ]);
	//      $response = $this->patch('home5');
	//
	//      $response->assertSee('Hello World');
	//  }
	//
	//  public function testCallOptions()
	//  {
	//      $this->withRoutes([
	//          [
	//              'options',
	//              'home6',
	//              function () {
	//                  return 'Hello World';
	//              },
	//          ],
	//      ]);
	//      $response = $this->options('home6');
	//
	//      $response->assertSee('Hello World');
	//  }
	//
	//  public function testCallDelete()
	//  {
	//      $this->withRoutes([
	//          [
	//              'delete',
	//              'home7',
	//              function () {
	//                  return 'Hello World';
	//              },
	//          ],
	//      ]);
	//      $response = $this->delete('home7');
	//
	//      $response->assertSee('Hello World');
	//  }
	//
	//  public function testSession()
	//  {
	//      $response = $this->withSession([
	//          'fruit'    => 'apple',
	//          'greeting' => 'hello',
	//      ])->get('home');
	//
	//      $response->assertSessionHas('fruit', 'apple');
	//      $response->assertSessionMissing('popcorn');
	//  }
	//
	//  public function testResponseReturned()
	//  {
	//      $response = $this->withRoutes([
	//          [
	//              'get',
	//              'yo',
	//              'Tests\Support\Controllers\Popcorn::index',
	//              ['hostname'=>TESTER],
	//          ],
	//      ])->get('home');
	//
	//      $response->assertSee('Hello');
	//  }
	//
	//  public function testResponseEchoed()
	//  {
	//      $response = $this->withRoutes([
	//          [
	//              'get',
	//              'dude',
	//              'Tests\Support\Controllers\Popcorn::canyon',
	//              ['hostname'=>TESTER],
	//          ],
	//      ])->get('dude');
	//
	//      $response->assertSee('Hello-o-o');
	//  }

	//  public function testResponseResponded()
	//  {
	//      $response = $this->withSession([
	//          'fruit'    => 'apple',
	//          'greeting' => 'hello',
	//      ])->get('home');
	//
	//      $response->assertSessionHas('fruit', 'apple');
	//      $response->assertSessionMissing('popcorn');
	//  }
	//
	//  public function testResponseJSON()
	//  {
	//      $response = $this->withSession([
	//          'fruit'    => 'apple',
	//          'greeting' => 'hello',
	//      ])->get('home');
	//
	//      $response->assertSessionHas('fruit', 'apple');
	//      $response->assertSessionMissing('popcorn');
	//  }
	//
	//  public function testResponseXML()
	//  {
	//      $response = $this->withSession([
	//          'fruit'    => 'apple',
	//          'greeting' => 'hello',
	//      ])->get('home');
	//
	//      $response->assertSessionHas('fruit', 'apple');
	//      $response->assertSessionMissing('popcorn');
	//  }

}
