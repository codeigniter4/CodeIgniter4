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

namespace CodeIgniter\Debug;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Toolbar as ToolbarConfig;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('Others')]
final class ToolbarTest extends CIUnitTestCase
{
    private ToolbarConfig $config;
    private ?IncomingRequest $request    = null;
    private ?ResponseInterface $response = null;

    protected function setUp(): void
    {
        parent::setUp();
        Services::reset();

        $this->config = new ToolbarConfig();

        // Mock CodeIgniter core service to provide performance stats
        $app = $this->createMock(CodeIgniter::class);
        $app->method('getPerformanceStats')->willReturn([
            'startTime' => microtime(true),
            'totalTime' => 0.05,
        ]);
        Services::injectMock('codeigniter', $app);
    }

    public function testPrepareRespectsDisableOnHeaders(): void
    {
        // Set up the new configuration property
        $this->config->disableOnHeaders = ['HX-Request'];
        Factories::injectMock('config', 'Toolbar', $this->config);

        // Initialize Request with the custom header
        $this->request = service('incomingrequest', null, false);
        $this->request->setHeader('HX-Request', 'true');

        // Initialize Response
        $this->response = service('response', null, false);
        $this->response->setBody('<html><body>Content</body></html>');
        $this->response->setHeader('Content-Type', 'text/html');

        $toolbar = new Toolbar($this->config);
        $toolbar->prepare($this->request, $this->response);

        // Assertions
        $this->assertTrue($this->response->hasHeader('Debugbar-Time'));
        $this->assertStringNotContainsString('id="debugbar_loader"', (string) $this->response->getBody());
    }

    public function testPrepareInjectsNormallyWithoutIgnoredHeader(): void
    {
        $this->config->disableOnHeaders = ['HX-Request'];
        Factories::injectMock('config', 'Toolbar', $this->config);

        $this->request  = service('incomingrequest', null, false);
        $this->response = service('response', null, false);
        $this->response->setBody('<html><body>Content</body></html>');
        $this->response->setHeader('Content-Type', 'text/html');

        $toolbar = new Toolbar($this->config);
        $toolbar->prepare($this->request, $this->response);

        // Assertions
        $this->assertStringContainsString('id="debugbar_loader"', (string) $this->response->getBody());
    }
}

/**
 * Mock is_cli() to return false within this namespace.
 */
function is_cli(): bool
{
    return false;
}
