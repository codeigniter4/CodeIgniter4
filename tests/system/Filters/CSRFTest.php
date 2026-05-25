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

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockSecurity;
use Config\Security as SecurityConfig;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('Others')]
final class CSRFTest extends CIUnitTestCase
{
    private \Config\Filters $config;
    private CLIRequest|IncomingRequest $request;
    private ?Response $response = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new \Config\Filters();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->resetServices();
        Factories::reset('config');
    }

    public function testDoNotCheckCliRequest(): void
    {
        $this->config->globals = [
            'before' => ['csrf'],
            'after'  => [],
        ];

        $this->request  = single_service('clirequest', null);
        $this->response = single_service('response');

        $filters = new Filters($this->config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        $request = $filters->run($uri, 'before');

        $this->assertSame($this->request, $request);
    }

    public function testPassGetRequest(): void
    {
        $this->config->globals = [
            'before' => ['csrf'],
            'after'  => [],
        ];

        $this->request  = single_service('incomingrequest', null);
        $this->response = single_service('response');

        $filters = new Filters($this->config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        $request = $filters->run($uri, 'before');

        // GET request is not protected, so no SecurityException will be thrown.
        $this->assertSame($this->request, $request);
    }

    public function testBeforeAddsVaryHeaderForFetchMetadataVerification(): void
    {
        $filter  = new CSRF();
        $request = single_service('incomingrequest', null)
            ->withMethod('POST')
            ->setHeader('Sec-Fetch-Site', 'same-origin');

        $filter->before($request);

        $this->assertSame('Sec-Fetch-Site', service('response')->getHeaderLine('Vary'));
    }

    public function testBeforeAppendsVaryHeaderForFetchMetadataVerification(): void
    {
        $filter  = new CSRF();
        $request = single_service('incomingrequest', null)
            ->withMethod('POST')
            ->setHeader('Sec-Fetch-Site', 'same-origin');
        service('response')->setHeader('Vary', 'Accept-Language');

        $filter->before($request);

        $this->assertSame('Accept-Language, Sec-Fetch-Site', service('response')->getHeaderLine('Vary'));
    }

    public function testBeforeAddsVaryHeaderToRedirectResponseForFetchMetadataVerification(): void
    {
        $config           = new SecurityConfig();
        $config->redirect = true;
        Factories::injectMock('config', 'Security', $config);

        $filter  = new CSRF();
        $request = single_service('incomingrequest', null)
            ->withMethod('POST')
            ->setHeader('Sec-Fetch-Site', 'cross-site');

        $response = $filter->before($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('Sec-Fetch-Site', $response->getHeaderLine('Vary'));
    }

    public function testBeforeThrowsExceptionForRejectedFetchMetadataVerification(): void
    {
        $filter  = new CSRF();
        $request = single_service('incomingrequest', null)
            ->withMethod('POST')
            ->setHeader('Sec-Fetch-Site', 'cross-site');

        try {
            $filter->before($request);

            $this->fail('Expected SecurityException was not thrown.');
        } catch (SecurityException) {
            $this->assertSame('Sec-Fetch-Site', service('response')->getHeaderLine('Vary'));
        }
    }

    public function testBeforeUsesSecurityServiceConfigForVaryHeader(): void
    {
        service('superglobals')
            ->setServer('REQUEST_METHOD', 'POST')
            ->setPost('csrf_test_name', '8b9218a55906f9dcc1dc263dce7f005a')
            ->setCookie('csrf_cookie_name', '8b9218a55906f9dcc1dc263dce7f005a');

        $config                    = new SecurityConfig();
        $config->csrfFetchMetadata = false;
        Services::injectMock('security', new MockSecurity($config));

        $filter  = new CSRF();
        $request = single_service('incomingrequest', null)
            ->withMethod('POST')
            ->setHeader('Sec-Fetch-Site', 'same-origin');

        $filter->before($request);

        $this->assertSame('', service('response')->getHeaderLine('Vary'));
    }
}
