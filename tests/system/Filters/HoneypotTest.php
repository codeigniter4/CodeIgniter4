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

namespace CodeIgniter\Filters;

use CodeIgniter\Honeypot\Exceptions\HoneypotException;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Honeypot;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('SeparateProcess')]
final class HoneypotTest extends CIUnitTestCase
{
    private \Config\Filters $config;
    private Honeypot $honey;

    /**
     * @var CLIRequest|IncomingRequest
     */
    private RequestInterface $request;

    private ?Response $response = null;

    #[WithoutErrorHandler]
    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new \Config\Filters();
        $this->honey  = new Honeypot();

        unset($_POST[$this->honey->name]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST[$this->honey->name] = 'hey';
    }

    public function testBeforeTriggered(): void
    {
        $this->config->globals = [
            'before' => ['honeypot'],
            'after'  => [],
        ];

        $this->request  = service('request', null, false);
        $this->response = service('response');

        $filters = new Filters($this->config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        $this->expectException(HoneypotException::class);
        $filters->run($uri, 'before');
    }

    public function testBeforeClean(): void
    {
        $this->config->globals = [
            'before' => ['honeypot'],
            'after'  => [],
        ];

        unset($_POST[$this->honey->name]);
        $this->request  = service('request', null, false);
        $this->response = service('response');

        $expected = $this->request;

        $filters = new Filters($this->config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        $request = $filters->run($uri, 'before');
        $this->assertSame($expected, $request);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testAfter(): void
    {
        $this->config->globals = [
            'before' => [],
            'after'  => ['honeypot'],
        ];

        $this->request  = service('request', null, false);
        $this->response = service('response');

        $filters = new Filters($this->config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        $this->response->setBody('<form></form>');
        $this->response = $filters->run($uri, 'after');
        $this->assertStringContainsString($this->honey->name, $this->response->getBody());
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testAfterNotApplicable(): void
    {
        $this->config->globals = [
            'before' => [],
            'after'  => ['honeypot'],
        ];

        $this->request  = service('request', null, false);
        $this->response = service('response');

        $filters = new Filters($this->config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        $this->response->setBody('<div></div>');
        $this->response = $filters->run($uri, 'after');
        $this->assertStringNotContainsString($this->honey->name, $this->response->getBody());
    }
}
