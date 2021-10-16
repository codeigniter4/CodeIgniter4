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

use App\Controllers\Home;
use App\Controllers\NeverHeardOfIt;
use CodeIgniter\Log\Logger;
use CodeIgniter\Test\Mock\MockLogger as LoggerConfig;
use Config\App;
use Config\Services;
use InvalidArgumentException;
use Tests\Support\Controllers\Popcorn;

/**
 * Exercise our Controller class.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState         disabled
 *
 * @internal
 */
final class ControllerTestTraitTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    public function testBadController()
    {
        $this->expectException(InvalidArgumentException::class);
        $logger = new Logger(new LoggerConfig());
        $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(NeverHeardOfIt::class)
            ->execute('index');
    }

    public function testBadControllerMethod()
    {
        $this->expectException(InvalidArgumentException::class);
        $logger = new Logger(new LoggerConfig());
        $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Home::class)
            ->execute('nothere');
    }

    public function testController()
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Home::class)
            ->execute('index');

        $this->assertTrue($result->isOK());
    }

    public function testControllerWithoutLogger()
    {
        $result = $this->withURI('http://example.com')
            ->controller(Home::class)
            ->execute('index');

        $this->assertTrue($result->isOK());
    }

    public function testPopcornIndex()
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('index');

        $this->assertTrue($result->isOK());
    }

    public function testPopcornIndex2()
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('index');

        $body = $result->response()->getBody();
        $this->assertSame('Hi there', $body);
    }

    public function testPopcornFailure()
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('pop');

        $this->assertSame(567, $result->response()->getStatusCode());
    }

    public function testPopcornException()
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('popper');

        $this->assertSame(500, $result->response()->getStatusCode());
    }

    public function testPopcornIndexWithSupport()
    {
        $logger = new Logger(new LoggerConfig());
        $config = new App();
        $body   = '';

        $result = $this->withURI('http://example.com')
            ->withConfig($config)
            ->withRequest(Services::request($config))
            ->withResponse(Services::response($config))
            ->withLogger($logger)
            ->withBody($body)
            ->controller(Popcorn::class)
            ->execute('index');

        $body = $result->response()->getBody();
        $this->assertSame('Hi there', $body);
    }

    public function testRequestPassthrough()
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('popper');

        $req = $result->request();
        $this->assertSame('get', $req->getMethod());
    }

    public function testFailureResponse()
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('oops');

        $this->assertFalse($result->isOK());
        $this->assertSame(401, $result->response()->getStatusCode());
    }

    public function testEmptyResponse()
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('weasel');

        $body = $result->response()->getBody(); // empty
        $this->assertEmpty($body);
        $this->assertFalse($result->isOK());
    }

    public function testRedirect()
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('goaway');

        $this->assertTrue($result->isRedirect());
    }

    public function testDOMParserForward()
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('index');

        $this->assertTrue($result->see('Hi'));
    }

    public function testFailsForward()
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('index');

        // won't fail, but doesn't do anything
        $this->assertNull($result->ohno('Hi'));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1834
     */
    public function testResponseOverriding()
    {
        $result = $this->withURI('http://example.com/rest/')
            ->controller(Popcorn::class)
            ->execute('index3');

        $response = json_decode($result->response()->getBody());
        $this->assertSame('en', $response->lang);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2470
     */
    public function testControllerNoURI()
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withLogger($logger)
            ->controller(Home::class)
            ->execute('index');

        $this->assertTrue($result->isOK());
    }

    public function testRedirectRoute()
    {
        $result = $this->controller(Popcorn::class)
            ->execute('toindex');
        $this->assertTrue($result->isRedirect());
    }
}
