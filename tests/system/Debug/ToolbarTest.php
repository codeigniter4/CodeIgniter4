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
use CodeIgniter\Test\Utilities\NativeHeadersStack;
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

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Load the mock once for the whole test class
        require_once SUPPORTPATH . 'Mock/MockNativeHeaders.php';
    }

    protected function setUp(): void
    {
        parent::setUp();

        NativeHeadersStack::reset();

        Services::reset();

        is_cli(false);

        $this->config = new ToolbarConfig();

        // Mock CodeIgniter core service to provide performance stats
        $app = $this->createMock(CodeIgniter::class);
        $app->method('getPerformanceStats')->willReturn([
            'startTime' => microtime(true),
            'totalTime' => 0.05,
        ]);
        Services::injectMock('codeigniter', $app);
    }

    protected function tearDown(): void
    {
        // Restore is_cli state
        is_cli(true);

        parent::tearDown();
    }

    public function testPrepareRespectsDisableOnHeaders(): void
    {
        // Set up the new configuration property
        $this->config->disableOnHeaders = ['HX-Request' => 'true'];
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
        $this->config->disableOnHeaders = ['HX-Request' => 'true'];
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

    // -------------------------------------------------------------------------
    // Native Header Conflicts
    // -------------------------------------------------------------------------

    public function testPrepareAbortsIfHeadersAlreadySent(): void
    {
        // Headers explicitly sent (e.g., echo before execution)
        NativeHeadersStack::$headersSent = true;

        $this->request  = service('incomingrequest', null, false);
        $this->response = service('response', null, false);
        $this->response->setBody('<html><body>Content</body></html>');

        $toolbar = new Toolbar($this->config);
        $toolbar->prepare($this->request, $this->response);

        // Must NOT inject because we can't modify the body safely
        $this->assertStringNotContainsString('id="debugbar_loader"', (string) $this->response->getBody());
    }

    public function testPrepareAbortsIfNativeContentTypeIsNotHtml(): void
    {
        // A library (like Dompdf) set a PDF header directly
        NativeHeadersStack::push('Content-Type: application/pdf');

        $this->request  = service('incomingrequest', null, false);
        $this->response = service('response', null, false);
        // Even if the body looks like HTML (before rendering), the header says PDF
        $this->response->setBody('<html><body>Raw PDF Data</body></html>');

        $toolbar = new Toolbar($this->config);
        $toolbar->prepare($this->request, $this->response);

        // Must NOT inject into non-HTML content
        $this->assertStringNotContainsString('id="debugbar_loader"', (string) $this->response->getBody());
    }

    public function testPrepareAbortsIfNativeContentDispositionIsAttachment(): void
    {
        // A file download (even if it is HTML)
        NativeHeadersStack::$headers = [
            'Content-Type: text/html',
            'Content-Disposition: attachment; filename="report.html"',
        ];

        $this->request  = service('incomingrequest', null, false);
        $this->response = service('response', null, false);
        $this->response->setBody('<html><body>Downloadable Report</body></html>');

        $toolbar = new Toolbar($this->config);
        $toolbar->prepare($this->request, $this->response);

        // Must NOT inject into downloads
        $this->assertStringNotContainsString('id="debugbar_loader"', (string) $this->response->getBody());
    }

    public function testPrepareWorksWithNativeHtmlHeader(): void
    {
        // Standard scenario where PHP header is text/html
        NativeHeadersStack::push('Content-Type: text/html; charset=UTF-8');

        $this->request  = service('incomingrequest', null, false);
        $this->response = service('response', null, false);
        $this->response->setBody('<html><body>Valid Page</body></html>');

        $toolbar = new Toolbar($this->config);
        $toolbar->prepare($this->request, $this->response);

        // Should inject normally
        $this->assertStringContainsString('id="debugbar_loader"', (string) $this->response->getBody());
    }
}
