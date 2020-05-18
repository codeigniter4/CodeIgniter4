<?php
namespace CodeIgniter\Filters;

use CodeIgniter\Config\Services;
use Config\Filters as FilterConfig;

/**
 * @backupGlobals enabled
 */
class DebugToolbarTest extends \CodeIgniter\Test\CIUnitTestCase
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

	public function testDebugToolbarFilterExcept()
	{
		$data     = [
			'testString' => 'bar',
			'bar'        => 'baz',
		];
		$expected = '<h1>bar</h1>';
		$this->assertEquals($expected, view('\Tests\Support\View\Views\simple', $data, []));
	}

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
		$this->assertEquals($expectedBefore, $this->request);

		// nothing should change here, since we are running in the CLI
		$filter->after($this->request, $this->response);
		$this->assertEquals($expectedAfter, $this->response);
	}

	public function testDebugToolbarFilterView()
	{
		$filter = new DebugToolbar();
		$filter->before($this->request);
		$data     = [
			'testString' => 'bar',
			'bar'        => 'baz',
		];
		$expected = 'DEBUG-VIEW';
		$this->assertStringContainsString($expected, view('\Tests\Support\View\Views\simple', $data, []));
	}

}
