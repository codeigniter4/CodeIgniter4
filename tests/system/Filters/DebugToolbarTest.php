<?php

namespace CodeIgniter\Filters;

use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Filters as FilterConfig;

/**
 * @backupGlobals enabled
 *
 * @internal
 */
final class DebugToolbarTest extends CIUnitTestCase
{
    protected $request;
    protected $response;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request  = Services::request();
        $this->response = Services::response();
    }

    //--------------------------------------------------------------------

    public function testDebugToolbarFilter()
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
