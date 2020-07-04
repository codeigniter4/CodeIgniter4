<?php
namespace CodeIgniter\Filters;

use CodeIgniter\Config\Services;
use CodeIgniter\Filters\Exceptions\FilterException;
use CodeIgniter\HTTP\ResponseInterface;

require_once __DIR__ . '/fixtures/ArgumentsFilter.php';

/**
 * @backupGlobals enabled
 */
class FiltersWithArgumentsTest extends \CodeIgniter\Test\CIUnitTestCase
{

	protected $request;
	protected $response;

	protected function setUp(): void
	{
		parent::setUp();

		$this->request  = Services::request();
		$this->response = Services::response();
	}

	public function testEnableFilterWithArguments()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => ['args' => 'CodeIgniter\Filters\fixtures\ArgumentsFilter'],
			'globals' => [
				'before' => [],
				'after'  => [],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);

		$filters = $filters->initialize('admin/foo/bar');

		$filters->enableFilter('args:admin , super', 'before');

		$found = $filters->getFilters();

		$this->assertTrue(in_array('args', $found['before']));
		$this->assertEquals(['admin', 'super'], $filters->getArguments('args'));
		$this->assertEquals(['args' => ['admin', 'super']], $filters->getArguments());
	}

	public function testNoArguments()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => ['args' => 'CodeIgniter\Filters\fixtures\ArgumentsFilter'],
			'globals' => [
				'before' => ['args'],
				'after'  => ['args'],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$response = $filters->run($uri, 'before');
		$this->assertEquals('You gave before() no arguments', $response);
		$response = $filters->run($uri, 'after');
		$this->assertEquals('You gave after() no arguments', $response->getBody());
	}

	public function testWithArguments()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => ['args' => 'CodeIgniter\Filters\fixtures\ArgumentsFilter'],
			'globals' => [
				'before' => ['args'],
				'after'  => ['args'],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$filters = $filters->initialize('admin/foo/bar');
		$filters->enableFilter('args:admin,root', 'before');
		$response = $filters->run($uri, 'before');
		$this->assertEquals('You gave before() arguments admin,root', $response);
		$response = $filters->run($uri, 'after');
		$this->assertEquals('You gave after() arguments admin,root', $response->getBody());
	}

}
