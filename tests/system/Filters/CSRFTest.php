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
 *
 * @group Others
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

    public function testDoNotCheckCliRequest()
    {
        $this->config->globals = [
            'before' => ['csrf'],
            'after'  => [],
        ];

        $this->request  = Services::clirequest(null, false);
        $this->response = Services::response();

        $filters = new Filters($this->config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        $request = $filters->run($uri, 'before');

        $this->assertSame($this->request, $request);
    }

    public function testPassGetRequest()
    {
        $this->config->globals = [
            'before' => ['csrf'],
            'after'  => [],
        ];

        $this->request  = Services::incomingrequest(null, false);
        $this->response = Services::response();

        $filters = new Filters($this->config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        $request = $filters->run($uri, 'before');

        // GET request is not protected, so no SecurityException will be thrown.
        $this->assertSame($this->request, $request);
    }
}
