<?php

namespace CodeIgniter\Test;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Response;

/**
 * @group                       DatabaseLive
 * @runTestsInSeparateProcesses
 * @preserveGlobalState         disabled
 *
 * @internal
 */
final class FeatureTestTraitTest extends CIUnitTestCase
{
    use FeatureTestTrait;

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
                static function () {
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
                static function () {
                    return 'Hello Earth';
                },
            ],
        ]);
        $response = $this->call('get', 'home');

        $this->assertInstanceOf(TestResponse::class, $response);
        $this->assertInstanceOf(Response::class, $response->response());
        $this->assertTrue($response->isOK());
        $this->assertSame('Hello Earth', $response->response()->getBody());
        $this->assertSame(200, $response->response()->getStatusCode());
    }

    public function testCallPost()
    {
        $this->withRoutes([
            [
                'post',
                'home',
                static function () {
                    return 'Hello Mars';
                },
            ],
        ]);
        $response = $this->post('home');

        $response->assertSee('Hello Mars');
    }

    public function testCallPostWithBody()
    {
        $this->withRoutes([
            [
                'post',
                'home',
                static function () {
                    return 'Hello ' . service('request')->getPost('foo') . '!';
                },
            ],
        ]);
        $response = $this->post('home', ['foo' => 'Mars']);

        $response->assertSee('Hello Mars!');
    }

    public function testCallPut()
    {
        $this->withRoutes([
            [
                'put',
                'home',
                static function () {
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
                static function () {
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
                static function () {
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
                static function () {
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
                static function () {
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

    public function testWithSessionNull()
    {
        $_SESSION = [
            'fruit'    => 'apple',
            'greeting' => 'hello',
        ];

        $response = $this->withRoutes([
            [
                'get',
                'home',
                static function () {
                    return 'Home';
                },
            ],
        ])->withSession()->get('home');

        $response->assertSessionHas('fruit', 'apple');
        $response->assertSessionMissing('popcorn');
    }

    public function testReturns()
    {
        $this->withRoutes([
            [
                'get',
                'home',
                '\Tests\Support\Controllers\Popcorn::index',
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
                '\Tests\Support\Controllers\Popcorn::cat',
            ],
        ]);
        $response = $this->get('home');
        $response->assertEmpty($response->response()->getBody());
    }

    public function testEchoesWithParams()
    {
        $this->withRoutes([
            [
                'get',
                'home',
                '\Tests\Support\Controllers\Popcorn::canyon',
            ],
        ]);

        $response = $this->get('home', ['foo' => 'bar']);
        $response->assertSee('Hello-o-o bar');
    }

    public function testEchoesWithQuery()
    {
        $this->withRoutes([
            [
                'get',
                'home',
                '\Tests\Support\Controllers\Popcorn::canyon',
            ],
        ]);

        $response = $this->get('home?foo=bar');
        $response->assertSee('Hello-o-o bar');
    }

    public function testCallZeroAsPathGot404()
    {
        $this->expectException(PageNotFoundException::class);
        $this->get('0');
    }

    public function provideRoutesData()
    {
        return [
            'non parameterized cli' => [
                'hello',
                'Hello::index',
                'Hello',
            ],
            'parameterized cli' => [
                'hello/(:any)',
                'Hello::index/$1',
                'Hello/index/samsonasik',
            ],
            'default method index' => [
                'hello',
                'Hello',
                'Hello',
            ],
            'capitalized controller and/or method' => [
                'hello',
                'Hello',
                'HELLO/INDEX',
            ],
        ];
    }

    /**
     * @dataProvider provideRoutesData
     */
    public function testOpenCliRoutesFromHttpGot404($from, $to, $httpGet)
    {
        $this->expectException(PageNotFoundException::class);

        require_once SUPPORTPATH . 'Controllers/Hello.php';

        $this->withRoutes([
            [
                'cli',
                $from,
                $to,
            ],
        ]);
        $this->get($httpGet);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3072
     */
    public function testIsOkWithRedirects()
    {
        $this->withRoutes([
            [
                'get',
                'home',
                '\Tests\Support\Controllers\Popcorn::goaway',
            ],
        ]);
        $response = $this->get('home');
        $this->assertTrue($response->isRedirect());
        $this->assertTrue($response->isOK());
    }

    public function testCallWithJsonRequest()
    {
        $this->withRoutes([
            [
                'post',
                'home',
                '\Tests\Support\Controllers\Popcorn::echoJson',
            ],
        ]);
        $response = $this->withBodyFormat('json')->call('post', 'home', ['foo' => 'bar']);
        $response->assertOK();
        $response->assertJSONExact(['foo' => 'bar']);
    }

    public function testCallWithJsonRequestObject()
    {
        $this->withRoutes([
            [
                'post',
                'home',
                '\Tests\Support\Controllers\Popcorn::echoJson',
            ],
        ]);
        $response = $this->withBodyFormat('json')->call('post', 'home', ['foo' => 'bar']);
        $response->assertOK();
        $response->assertJSONExact((object) ['foo' => 'bar']);
    }

    public function testSetupRequestBodyWithParams()
    {
        $request = $this->setupRequest('post', 'home');

        $request = $this->withBodyFormat('json')->setRequestBody($request, ['foo1' => 'bar1']);

        $this->assertJsonStringEqualsJsonString(json_encode(['foo1' => 'bar1']), $request->getBody());
        $this->assertTrue($request->header('Content-Type')->getValue() === 'application/json');
    }

    public function testSetupRequestBodyWithXml()
    {
        $request = $this->setupRequest('post', 'home');

        $request = $this->withBodyFormat('xml')->setRequestBody($request, ['foo' => 'bar']);

        $expectedXml = '<?xml version="1.0"?>
<response><foo>bar</foo></response>
';

        $this->assertSame($expectedXml, $request->getBody());
        $this->assertTrue($request->header('Content-Type')->getValue() === 'application/xml');
    }

    public function testSetupRequestBodyWithBody()
    {
        $request = $this->setupRequest('post', 'home');

        $request = $this->withBody('test')->setRequestBody($request);

        $this->assertSame('test', $request->getBody());
    }
}
