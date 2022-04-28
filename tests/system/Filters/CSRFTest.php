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
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @backupGlobals enabled
 *
 * @internal
 */
final class CSRFTest extends CIUnitTestCase
{
    private $config;
    private $request;
    private $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new \Config\Filters();
    }

    public function testNormal()
    {
        $this->config->globals = [
            'before' => ['csrf'],
            'after'  => [],
        ];

        $this->request  = Services::request(null, false);
        $this->response = Services::response();

        $filters = new Filters($this->config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        // we expect CSRF requests to be ignored in CLI
        $expected = $this->request;
        $request  = $filters->run($uri, 'before');
        $this->assertSame($expected, $request);
    }
}
