<?php

declare(strict_types=1);

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
use CodeIgniter\Controller;
use CodeIgniter\Log\Logger;
use CodeIgniter\Test\Mock\MockLogger as LoggerConfig;
use Config\App;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Tests\Support\Controllers\Newautorouting;
use Tests\Support\Controllers\Popcorn;

/**
 * Exercise our Controller class.
 *
 * @internal
 */
#[Group('SeparateProcess')]
#[PreserveGlobalState(false)]
#[RunTestsInSeparateProcesses]
final class ControllerTestTraitTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    #[WithoutErrorHandler]
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testBadController(): void
    {
        $this->expectException('InvalidArgumentException');
        $logger = new Logger(new LoggerConfig());
        $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(NeverHeardOfIt::class)
            ->execute('index');
    }

    public function testBadControllerMethod(): void
    {
        $this->expectException('InvalidArgumentException');
        $logger = new Logger(new LoggerConfig());
        $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Home::class)
            ->execute('nothere');
    }

    public function testController(): void
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Home::class)
            ->execute('index');

        $this->assertTrue($result->isOK());
    }

    public function testControllerWithoutLogger(): void
    {
        $result = $this->withURI('http://example.com')
            ->controller(Home::class)
            ->execute('index');

        $this->assertTrue($result->isOK());
    }

    public function testPopcornIndex(): void
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('index');

        $this->assertTrue($result->isOK());
    }

    public function testPopcornIndex2(): void
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('index');

        $body = $result->response()->getBody();
        $this->assertSame('Hi there', $body);
    }

    public function testPopcornFailure(): void
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('pop');

        $this->assertSame(567, $result->response()->getStatusCode());
    }

    public function testPopcornException(): void
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('popper');

        $this->assertSame(500, $result->response()->getStatusCode());
    }

    public function testPopcornIndexWithSupport(): void
    {
        $logger = new Logger(new LoggerConfig());
        $config = new App();
        $body   = '';

        $result = $this->withURI('http://example.com')
            ->withConfig($config)
            ->withRequest(service('request', $config))
            ->withResponse(service('response', $config))
            ->withLogger($logger)
            ->withBody($body)
            ->controller(Popcorn::class)
            ->execute('index');

        $body = $result->response()->getBody();
        $this->assertSame('Hi there', $body);
    }

    public function testRequestPassthrough(): void
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('popper');

        $req = $result->request();
        $this->assertSame('GET', $req->getMethod());
    }

    public function testFailureResponse(): void
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('oops');

        $this->assertFalse($result->isOK());
        $this->assertSame(401, $result->response()->getStatusCode());
    }

    public function testEmptyResponse(): void
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('weasel');

        $body = $result->response()->getBody(); // empty string as JSON
        $this->assertSame('""', $body);
        $this->assertTrue($result->isOK());
    }

    public function testRedirect(): void
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('goaway');

        $this->assertTrue($result->isRedirect());
    }

    public function testDOMParserForward(): void
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withURI('http://example.com')
            ->withLogger($logger)
            ->controller(Popcorn::class)
            ->execute('index');

        $this->assertTrue($result->see('Hi'));
    }

    public function testFailsForward(): void
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
    public function testResponseOverriding(): void
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
    public function testControllerNoURI(): void
    {
        $logger = new Logger(new LoggerConfig());
        $result = $this->withLogger($logger)
            ->controller(Home::class)
            ->execute('index');

        $this->assertTrue($result->isOK());
    }

    public function testRedirectRoute(): void
    {
        $result = $this->controller(Popcorn::class)
            ->execute('toindex');
        $this->assertTrue($result->isRedirect());
    }

    public function testUsesRequestBody(): void
    {
        $this->controller = new class () extends Controller {
            public function throwsBody(): never
            {
                throw new Exception($this->request->getBody());
            }
        };
        $this->controller->initController($this->request, $this->response, $this->logger);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('banana');

        $this->withBody('banana')->execute('throwsBody');
    }

    public function testWithUriUpdatesUriStringAndCurrentUrlValues(): void
    {
        $result = $this->withURI('http://example.com/foo/bar/1/2/3')
            ->controller(Newautorouting::class)
            ->execute('postSave', '1', '2', '3');

        $this->assertSame('Saved', $result->response()->getBody());
        $this->assertSame('foo/bar/1/2/3', uri_string());
        $this->assertSame('http://example.com/index.php/foo/bar/1/2/3', current_url());
    }
}
