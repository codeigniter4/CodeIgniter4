<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\Config\Factories;
use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use Config\App;
use Config\Routing;
use Config\Services;

/**
 * @group DatabaseLive
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

    protected function tearDown(): void
    {
        parent::tearDown();

        Events::simulate(false);

        $this->resetServices();
    }

    public function testCallGet(): void
    {
        $this->withRoutes([
            [
                'get',
                'home',
                static fn () => 'Hello World',
            ],
        ]);
        $response = $this->get('home');

        $this->assertInstanceOf(TestResponse::class, $response);
        $this->assertInstanceOf(Response::class, $response->response());
        $this->assertTrue($response->isOK());
        $this->assertSame('Hello World', $response->response()->getBody());
        $this->assertSame(200, $response->response()->getStatusCode());
        $response->assertSee('Hello World');
        $response->assertDontSee('Again');
    }

    public function testCallGetAndUriString(): void
    {
        $this->withRoutes([
            [
                'get',
                'foo/bar/1/2/3',
                static fn () => 'Hello World',
            ],
        ]);
        $response = $this->get('foo/bar/1/2/3');

        $this->assertSame('Hello World', $response->response()->getBody());
        $this->assertSame('foo/bar/1/2/3', uri_string());
        $this->assertSame('http://example.com/index.php/foo/bar/1/2/3', current_url());
    }

    public function testCallGetAndFilterReturnsResponse(): void
    {
        $this->withRoutes([
            [
                'get',
                'admin',
                static fn () => 'Admin Area',
                ['filter' => 'test-redirectfilter'],
            ],
        ]);
        $response = $this->get('admin');

        $response->assertRedirectTo('login');
    }

    public function testClosureWithEcho()
    {
        $this->withRoutes([
            [
                'get',
                'home',
                static function () { echo 'test echo'; },
            ],
        ]);

        $response = $this->get('home');
        $this->assertInstanceOf(TestResponse::class, $response);
        $this->assertInstanceOf(Response::class, $response->response());
        $this->assertTrue($response->isOK());
        $this->assertSame('test echo', $response->response()->getBody());
        $this->assertSame(200, $response->response()->getStatusCode());
    }

    public function testCallPost(): void
    {
        $this->withRoutes([
            [
                'post',
                'home',
                static fn () => 'Hello Mars',
            ],
        ]);
        $response = $this->post('home');

        $response->assertSee('Hello Mars');
    }

    public function testCallPostWithBody(): void
    {
        $this->withRoutes([
            [
                'post',
                'home',
                static fn () => 'Hello ' . service('request')->getPost('foo') . '!',
            ],
        ]);
        $response = $this->post('home', ['foo' => 'Mars']);

        $response->assertSee('Hello Mars!');
    }

    public function testCallValidationTwice(): void
    {
        $this->withRoutes([
            [
                'post',
                'section/create',
                static function () {
                    $validation = Services::validation();
                    $validation->setRule('title', 'title', 'required|min_length[3]');

                    $post = Services::request()->getPost();

                    if ($validation->run($post)) {
                        return 'Okay';
                    }

                    return 'Invalid';
                },
            ],
        ]);

        $response = $this->post('section/create', ['foo' => 'Mars']);

        $response->assertSee('Invalid');

        $response = $this->post('section/create', ['title' => 'Section Title']);

        $response->assertSee('Okay');
    }

    public function testCallPut(): void
    {
        $this->withRoutes([
            [
                'put',
                'home',
                static fn () => 'Hello Pluto',
            ],
        ]);
        $response = $this->put('home');

        $response->assertSee('Hello Pluto');
    }

    public function testCallPatch(): void
    {
        $this->withRoutes([
            [
                'patch',
                'home',
                static fn () => 'Hello Jupiter',
            ],
        ]);
        $response = $this->patch('home');

        $response->assertSee('Hello Jupiter');
    }

    public function testCallOptions(): void
    {
        $this->withRoutes([
            [
                'options',
                'home',
                static fn () => 'Hello George',
            ],
        ]);
        $response = $this->options('home');

        $response->assertSee('Hello George');
    }

    public function testCallDelete(): void
    {
        $this->withRoutes([
            [
                'delete',
                'home',
                static fn () => 'Hello Wonka',
            ],
        ]);
        $response = $this->delete('home');

        $response->assertSee('Hello Wonka');
    }

    public function testSession(): void
    {
        $response = $this->withRoutes([
            [
                'get',
                'home',
                static fn () => 'Home',
            ],
        ])->withSession([
            'fruit'    => 'apple',
            'greeting' => 'hello',
        ])->get('home');

        $response->assertSessionHas('fruit', 'apple');
        $response->assertSessionMissing('popcorn');
    }

    public function testWithSessionNull(): void
    {
        $_SESSION = [
            'fruit'    => 'apple',
            'greeting' => 'hello',
        ];

        $response = $this->withRoutes([
            [
                'get',
                'home',
                static fn () => 'Home',
            ],
        ])->withSession()->get('home');

        $response->assertSessionHas('fruit', 'apple');
        $response->assertSessionMissing('popcorn');
    }

    public function testReturns(): void
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

    public function testIgnores(): void
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

    public function testEchoesWithParams(): void
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

    public function testEchoesWithQuery(): void
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

    public function testCallZeroAsPathGot404(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->get('0');
    }

    public static function provideOpenCliRoutesFromHttpGot404(): iterable
    {
        return [
            'non parameterized cli' => [
                'hello',
                'Hello::index',
                'Hello',
            ],
            'parameterized param cli' => [
                'hello/(:any)',
                'Hello::index/$1',
                'Hello/index/samsonasik',
            ],
            'parameterized method cli' => [
                'hello/(:segment)',
                'Hello::$1',
                'Hello/index',
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
     * @dataProvider provideOpenCliRoutesFromHttpGot404
     *
     * @param mixed $from
     * @param mixed $to
     * @param mixed $httpGet
     */
    public function testOpenCliRoutesFromHttpGot404($from, $to, $httpGet): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Cannot access CLI Route: ');

        $collection = Services::routes();
        $collection->setAutoRoute(true);
        $collection->setDefaultNamespace('Tests\Support\Controllers');

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
    public function testIsOkWithRedirects(): void
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

    public function testCallGetWithParams(): void
    {
        $this->withRoutes([
            [
                'get',
                'home',
                static fn () => json_encode(Services::request()->getGet()),
            ],
        ]);
        $data = [
            'true'   => true,
            'false'  => false,
            'int'    => 2,
            'null'   => null,
            'float'  => 1.23,
            'string' => 'foo',
        ];
        $response = $this->get('home', $data);

        $response->assertOK();
        $this->assertStringContainsString(
            // All GET values will be strings.
            '{"true":"1","false":"","int":"2","null":"","float":"1.23","string":"foo"}',
            $response->getBody()
        );
    }

    public function testCallGetWithParamsAndREQUEST(): void
    {
        $this->withRoutes([
            [
                'get',
                'home',
                static fn () => json_encode(Services::request()->fetchGlobal('request')),
            ],
        ]);
        $data = [
            'true'   => true,
            'false'  => false,
            'int'    => 2,
            'null'   => null,
            'float'  => 1.23,
            'string' => 'foo',
        ];
        $response = $this->get('home', $data);

        $response->assertOK();
        $this->assertStringContainsString(
            // All GET values will be strings.
            '{"true":"1","false":"","int":"2","null":"","float":"1.23","string":"foo"}',
            $response->getBody()
        );
    }

    public function testCallPostWithParams(): void
    {
        $this->withRoutes([
            [
                'post',
                'home',
                static fn () => json_encode(Services::request()->getPost()),
            ],
        ]);
        $data = [
            'true'   => true,
            'false'  => false,
            'int'    => 2,
            'null'   => null,
            'float'  => 1.23,
            'string' => 'foo',
        ];
        $response = $this->post('home', $data);

        $response->assertOK();
        $this->assertStringContainsString(
            // All POST values will be strings.
            '{"true":"1","false":"","int":"2","null":"","float":"1.23","string":"foo"}',
            $response->getBody()
        );
    }

    public function testCallPostWithParamsAndREQUEST(): void
    {
        $this->withRoutes([
            [
                'post',
                'home',
                static fn () => json_encode(Services::request()->fetchGlobal('request')),
            ],
        ]);
        $data = [
            'true'   => true,
            'false'  => false,
            'int'    => 2,
            'null'   => null,
            'float'  => 1.23,
            'string' => 'foo',
        ];
        $response = $this->post('home', $data);

        $response->assertOK();
        $this->assertStringContainsString(
            // All POST values will be strings.
            '{"true":"1","false":"","int":"2","null":"","float":"1.23","string":"foo"}',
            $response->getBody()
        );
    }

    public function testCallPutWithJsonRequest(): void
    {
        $this->withRoutes([
            [
                'put',
                'home',
                '\Tests\Support\Controllers\Popcorn::echoJson',
            ],
        ]);
        $data = [
            'true'   => true,
            'false'  => false,
            'int'    => 2,
            'null'   => null,
            'float'  => 1.23,
            'string' => 'foo',
        ];
        $response = $this->withBodyFormat('json')
            ->call('put', 'home', $data);

        $response->assertOK();
        $response->assertJSONExact($data);
    }

    public function testCallPutWithJsonRequestAndREQUEST(): void
    {
        $this->withRoutes([
            [
                'put',
                'home',
                static fn () => json_encode(Services::request()->fetchGlobal('request')),
            ],
        ]);
        $data = [
            'true'   => true,
            'false'  => false,
            'int'    => 2,
            'null'   => null,
            'float'  => 1.23,
            'string' => 'foo',
        ];
        $response = $this->withBodyFormat('json')
            ->call('put', 'home', $data);

        $response->assertOK();
        $this->assertStringContainsString('[]', $response->getBody());
    }

    public function testCallWithJsonRequest(): void
    {
        $this->withRoutes([
            [
                'post',
                'home',
                '\Tests\Support\Controllers\Popcorn::echoJson',
            ],
        ]);
        $data = [
            'true'   => true,
            'false'  => false,
            'int'    => 2,
            'null'   => null,
            'float'  => 1.23,
            'string' => 'foo',
        ];
        $response = $this->withBodyFormat('json')
            ->call('post', 'home', $data);

        $response->assertOK();
        $response->assertJSONExact($data);
    }

    public function testSetupRequestBodyWithParams(): void
    {
        $request = $this->setupRequest('post', 'home');

        $request = $this->withBodyFormat('json')->setRequestBody($request, ['foo1' => 'bar1']);

        $this->assertJsonStringEqualsJsonString(json_encode(['foo1' => 'bar1']), $request->getBody());
        $this->assertSame('application/json', $request->header('Content-Type')->getValue());
    }

    public function testSetupJSONRequestBodyWithBody(): void
    {
        $request = $this->setupRequest('post', 'home');
        $request = $this->withBodyFormat('json')
            ->withBody(json_encode(['foo1' => 'bar1']))
            ->setRequestBody($request);

        $this->assertJsonStringEqualsJsonString(
            json_encode(['foo1' => 'bar1']),
            $request->getBody()
        );
        $this->assertSame(
            'application/json',
            $request->header('Content-Type')->getValue()
        );
    }

    public function testSetupRequestBodyWithXml(): void
    {
        $request = $this->setupRequest('post', 'home');

        $data = [
            'true'   => true,
            'false'  => false,
            'int'    => 2,
            'null'   => null,
            'float'  => 1.23,
            'string' => 'foo',
        ];
        $request = $this->withBodyFormat('xml')->setRequestBody($request, $data);

        $expectedXml = '<?xml version="1.0"?>
<response><true>1</true><false/><int>2</int><null/><float>1.23</float><string>foo</string></response>
';

        $this->assertSame($expectedXml, $request->getBody());
        $this->assertSame('application/xml', $request->header('Content-Type')->getValue());
    }

    public function testSetupRequestBodyWithBody(): void
    {
        $request = $this->setupRequest('post', 'home');

        $request = $this->withBody('test')->setRequestBody($request);

        $this->assertSame('test', $request->getBody());
    }

    public function testAutoRoutingLegacy()
    {
        $config            = config(Routing::class);
        $config->autoRoute = true;
        Factories::injectMock('config', Routing::class, $config);

        $response = $this->get('home/index');

        $response->assertOK();
    }

    public function testForceGlobalSecureRequests()
    {
        $config                            = config(App::class);
        $config->forceGlobalSecureRequests = true;
        Factories::injectMock('config', App::class, $config);

        $this->app = new MockCodeIgniter($config);
        $this->app->initialize();

        $response = $this->get('/');

        // Do not redirect.
        $response->assertStatus(200);
    }
}
