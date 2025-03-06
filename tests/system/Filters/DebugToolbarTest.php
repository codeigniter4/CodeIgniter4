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

use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Filters as FilterConfig;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('Others')]
final class DebugToolbarTest extends CIUnitTestCase
{
    /**
     * @var CLIRequest|IncomingRequest
     */
    private RequestInterface $request;

    private Response $response;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request  = service('request');
        $this->response = service('response');
    }

    public function testDebugToolbarFilter(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config          = new FilterConfig();
        $config->globals = [
            'before' => ['toolbar'], // not normal; exercising its before()
            'after'  => ['toolbar'],
        ];

        $filter = new DebugToolbar();

        $expectedBefore = $this->request;
        $expectedAfter  = $this->response;

        // nothing should change here, since we have no before logic
        $result = $filter->before($this->request);
        $this->assertSame($expectedBefore, $this->request);
        $this->assertNull($result);

        // nothing should change here, since we are running in the CLI
        $result = $filter->after($this->request, $this->response);
        $this->assertSame($expectedAfter, $this->response);
        $this->assertNotInstanceOf(ResponseInterface::class, $result);
    }
}
