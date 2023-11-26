<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Filters;

use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Filters as FilterConfig;

/**
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group Others
 */
final class DebugToolbarTest extends CIUnitTestCase
{
    /**
     * @var CLIRequest|IncomingRequest
     */
    private $request;

    private Response $response;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request  = Services::request();
        $this->response = Services::response();
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
        $filter->before($this->request);
        $this->assertSame($expectedBefore, $this->request);

        // nothing should change here, since we are running in the CLI
        $filter->after($this->request, $this->response);
        $this->assertSame($expectedAfter, $this->response);
    }
}
