<?php
namespace CodeIgniter\Filters;

use CodeIgniter\Config\Services;
use CodeIgniter\Filters\Exceptions\FilterException;
use CodeIgniter\HTTP\ResponseInterface;

require_once __DIR__ . '/fixtures/GoogleMe.php';
require_once __DIR__ . '/fixtures/GoogleYou.php';
require_once __DIR__ . '/fixtures/GoogleEmpty.php';
require_once __DIR__ . '/fixtures/GoogleCurious.php';
require_once __DIR__ . '/fixtures/InvalidClass.php';
require_once __DIR__ . '/fixtures/Multiple1.php';
require_once __DIR__ . '/fixtures/Multiple2.php';
require_once __DIR__ . '/fixtures/Role.php';

/**
 * @backupGlobals enabled
 */
class FiltersTest extends \CodeIgniter\Test\CIUnitTestCase
{

	protected $request;
	protected $response;

	protected function setUp(): void
	{
		parent::setUp();
		Services::reset();
		
		$defaults = [
			'Config' => APPPATH . 'Config',
			'App'    => APPPATH,
			'Tests\Support' => TESTPATH . '_support',
		];

		Services::autoloader()->addNamespace($defaults);
		
		$this->request  = Services::request();
		$this->response = Services::response();
		
	}

	//--------------------------------------------------------------------
	public function testProcessMethodDetectsCLI()
	{
		$config  = [
			'aliases' => ['foo' => ''],
			'methods' => [
				'cli' => ['foo'],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);

		$expected = [
			'before' => ['foo'],
			'after'  => [],
		];

		$this->assertEquals($expected, $filters->initialize()->getFilters());
	}

	//--------------------------------------------------------------------

	public function testProcessMethodDetectsGetRequests()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config  = [
			'aliases' => ['foo' => ''],
			'methods' => [
				'get' => ['foo'],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);

		$expected = [
			'before' => ['foo'],
			'after'  => [],
		];

		$this->assertEquals($expected, $filters->initialize()->getFilters());
	}

	
	//--------------------------------------------------------------------

	public function testProcessMethodRespectsMethod()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config  = [
			'aliases' => [
				'foo' => '',
				'bar' => '',
			],
			'methods' => [
				'post' => ['foo'],
				'get'  => ['bar'],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);

		$expected = [
			'before' => ['bar'],
			'after'  => [],
		];

		$this->assertEquals($expected, $filters->initialize()->getFilters());
	}

	public function testProcessMethodIgnoresMethod()
	{
		$_SERVER['REQUEST_METHOD'] = 'DELETE';

		$config  = [
			'aliases' => [
				'foo' => '',
				'bar' => '',
			],
			'methods' => [
				'post' => ['foo'],
				'get'  => ['bar'],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);

		$expected = [
			'before' => [],
			'after'  => [],
		];

		$this->assertEquals($expected, $filters->initialize()->getFilters());
	}

	//--------------------------------------------------------------------

	public function testProcessMethodProcessGlobals()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config  = [
			'aliases' => [
				'foo' => '',
				'bar' => '',
				'baz' => '',
			],
			'globals' => [
				'before' => [
					'foo' => ['bar'], // not excluded
					'bar'
				],
				'after'  => [
					'baz'
				],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);

		$expected = [
			'before' => [
				'foo',
				'bar',
			],
			'after'  => ['baz'],
		];

		$this->assertEquals($expected, $filters->initialize()->getFilters());
	}

	//--------------------------------------------------------------------

	public function provideExcept()
	{
		return [
			[
				['admin/*'],
			],
			[
				[],
			],
		];
	}

	/**
	 * @dataProvider provideExcept
	 */
	public function testProcessMethodProcessGlobalsWithExcept(array $except)
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config  = [
			'aliases' => [
				'foo' => '',
				'bar' => '',
				'baz' => '',
			],
			'globals' => [
				'before' => [
					'foo' => ['except' => $except],
					'bar'
				],
				'after'  => [
					'baz'
				],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$expected = [
			'before' => [
				'bar'
			],
			'after'  => ['baz'],
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	//--------------------------------------------------------------------

	public function testProcessMethodProcessesFiltersBefore()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config  = [
			'aliases' => [
				'foo' => '',
				'bar' => '',
				'baz' => '',
			],
			'filters' => [
				'foo' => [
					'before' => ['admin/*'],
					'after'  => ['/users/*'],
				],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$expected = [
			'before' => ['foo'],
			'after'  => [],
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	//--------------------------------------------------------------------

	public function testProcessMethodProcessesFiltersAfter()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config  = [
			'aliases' => [
				'foo' => '',
				'bar' => '',
				'baz' => '',
			],
			'filters' => [
				'foo' => [
					'before' => ['admin/*'],
					'after'  => ['/users/*'],
				],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'users/foo/bar';

		$expected = [
			'before' => [],
			'after'  => [
				'foo',
			],
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	//--------------------------------------------------------------------

	public function testProcessMethodProcessesCombined()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config  = [
			'aliases' => [
				'foog' => '',
				'barg' => '',
				'bazg' => '',
				'foo'  => '',
				'bar'  => '',
				'foof' => '',
			],
			'globals' => [
				'before' => [
					'foog' => ['except' => ['admin/*']],
					'barg'
				],
				'after'  => [
					'bazg'
				],
			],
			'methods' => [
				'post' => ['foo'],
				'get'  => ['bar'],
			],
			'filters' => [
				'foof' => [
					'before' => ['admin/*'],
					'after'  => ['/users/*'],
				],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$expected = [
			'before' => [
				'barg',
				'bar',
				'foof',
			],
			'after'  => ['bazg'],
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	//--------------------------------------------------------------------

	public function testProcessMethodProcessesCombinedAfterForToolbar()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config  = [
			'aliases' => [
				'toolbar' => '',
				'bazg'    => '',
				'bar'     => '',
				'foof'    => '',
			],
			'globals' => [
				'after' => [
					'toolbar',
					'bazg',
				],
			],
			'methods' => [
				'get' => ['bar'],
			],
			'filters' => [
				'foof' => [
					'after' => ['admin/*'],
				],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$expected = [
			'before' => ['bar'],
			'after'  => [
				'bazg',
				'foof',
				'toolbar',
			],
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	//--------------------------------------------------------------------

	public function testRunThrowsWithInvalidAlias()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => [],
			'globals' => [
				'before' => ['invalid'],
				'after'  => [],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);

		$this->expectException(FilterException::class);
		$uri = 'admin/foo/bar';

		$filters->run($uri);
	}
	
	//--------------------------------------------------------------------

	public function testCustomFiltersLoad()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => [],
			'globals' => [
				'before' => ['test-customfilter'],
				'after'  => [],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$request = $filters->run($uri, 'before');

		$this->assertEquals('http://hellowworld.com', $request->url);
	}	
	

	//--------------------------------------------------------------------

	public function testRunThrowsWithInvalidClassType()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => ['invalid' => 'CodeIgniter\Filters\fixtures\InvalidClass'],
			'globals' => [
				'before' => ['invalid'],
				'after'  => [],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);

		$this->expectException(FilterException::class);
		$uri = 'admin/foo/bar';

		$filters->run($uri);
	}

	//--------------------------------------------------------------------

	public function testRunDoesBefore()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => ['google' => 'CodeIgniter\Filters\fixtures\GoogleMe'],
			'globals' => [
				'before' => ['google'],
				'after'  => [],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$request = $filters->run($uri, 'before');

		$this->assertEquals('http://google.com', $request->url);
	}

	//--------------------------------------------------------------------

	public function testRunDoesAfter()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => ['google' => 'CodeIgniter\Filters\fixtures\GoogleMe'],
			'globals' => [
				'before' => [],
				'after'  => ['google'],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$response = $filters->run($uri, 'after');

		$this->assertEquals('http://google.com', $response->csp);
	}

	//--------------------------------------------------------------------

	public function testShortCircuit()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => ['banana' => 'CodeIgniter\Filters\fixtures\GoogleYou'],
			'globals' => [
				'before' => ['banana'],
				'after'  => [],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$response = $filters->run($uri, 'before');
		$this->assertTrue($response instanceof ResponseInterface);
		$this->assertEquals('http://google.com', $response->csp);
	}

	public function testOtherResult()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => [
				'nowhere' => 'CodeIgniter\Filters\fixtures\GoogleEmpty',
				'banana'  => 'CodeIgniter\Filters\fixtures\GoogleCurious',
			],
			'globals' => [
				'before' => [
					'nowhere',
					'banana',
				],
				'after'  => [],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$response = $filters->run($uri, 'before');

		$this->assertEquals('This is curious', $response);
	}

	//--------------------------------------------------------------------

	public function testBeforeExceptString()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config  = [
			'aliases' => [
				'foo' => '',
				'bar' => '',
				'baz' => '',
			],
			'globals' => [
				'before' => [
					'foo' => ['except' => 'admin/*'],
					'bar'
				],
				'after'  => [
					'baz'
				],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$expected = [
			'before' => [
				'bar'
			],
			'after'  => ['baz'],
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	public function testBeforeExceptInapplicable()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config  = [
			'aliases' => [
				'foo' => '',
				'bar' => '',
				'baz' => '',
			],
			'globals' => [
				'before' => [
					'foo' => ['except' => 'george/*'],
					'bar'
				],
				'after'  => [
					'baz'
				],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$expected = [
			'before' => [
				'foo',
				'bar',
			],
			'after'  => ['baz'],
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	public function testAfterExceptString()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config  = [
			'aliases' => [
				'foo' => '',
				'bar' => '',
				'baz' => '',
			],
			'globals' => [
				'before' => [
					'bar'
				],
				'after'  => [
					'foo' => ['except' => 'admin/*'],
					'baz'
				],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$expected = [
			'before' => [
				'bar'
			],
			'after'  => ['baz'],
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	public function testAfterExceptInapplicable()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config  = [
			'aliases' => [
				'foo' => '',
				'bar' => '',
				'baz' => '',
			],
			'globals' => [
				'before' => [
					'bar'
				],
				'after'  => [
					'foo' => ['except' => 'george/*'],
					'baz'
				],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$expected = [
			'before' => [
				'bar'
			],
			'after'  => [
				'foo',
				'baz',
			],
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	public function testAddFilter()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => ['google' => 'CodeIgniter\Filters\fixtures\GoogleMe'],
			'globals' => [
				'before' => ['google'],
				'after'  => [],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);

		$filters = $filters->addFilter('Some\Class', 'some_alias');

		$filters = $filters->initialize('admin/foo/bar');

		$filters = $filters->getFilters();

		$this->assertTrue(in_array('some_alias', $filters['before']));
	}

	public function testAddFilterSection()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [];

		$filters = new Filters((object) $config, $this->request, $this->response);

		$filters = $filters->addFilter('Some\OtherClass', 'another', 'before', 'globals')
				->initialize('admin/foo/bar');
		$list    = $filters->getFilters();

		$this->assertTrue(in_array('another', $list['before']));
	}

	public function testInitializeTwice()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [];

		$filters = new Filters((object) $config, $this->request, $this->response);

		$filters = $filters->addFilter('Some\OtherClass', 'another', 'before', 'globals')
				->initialize('admin/foo/bar')
				->initialize();
		$list    = $filters->getFilters();

		$this->assertTrue(in_array('another', $list['before']));
	}

	public function testEnableFilter()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => ['google' => 'CodeIgniter\Filters\fixtures\GoogleMe'],
			'globals' => [
				'before' => [],
				'after'  => [],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);

		$filters = $filters->initialize('admin/foo/bar');

		$filters->enableFilter('google', 'before');

		$filters = $filters->getFilters();

		$this->assertTrue(in_array('google', $filters['before']));
	}

	public function testEnableFilterWithArguments()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => ['role' => 'CodeIgniter\Filters\fixtures\Role'],
			'globals' => [
				'before' => [],
				'after'  => [],
			],
		];

		$filters = new Filters((object)$config, $this->request, $this->response);

		$filters = $filters->initialize('admin/foo/bar');

		$filters->enableFilter('role:admin , super', 'before');
		$filters->enableFilter('role:admin , super', 'after');

		$found = $filters->getFilters();

		$this->assertTrue(in_array('role', $found['before']));
		$this->assertEquals(['admin', 'super'], $filters->getArguments('role'));
		$this->assertEquals(['role' => ['admin', 'super']], $filters->getArguments());

		$response = $filters->run('admin/foo/bar', 'before');
		$this->assertEquals('admin;super', $response);

		$response = $filters->run('admin/foo/bar', 'after');
		$this->assertEquals('admin;super', $response->getBody());
	}

	public function testEnableFilterWithNoArguments()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => ['role' => 'CodeIgniter\Filters\fixtures\Role'],
			'globals' => [
				'before' => [],
				'after'  => [],
			],
		];

		$filters = new Filters((object)$config, $this->request, $this->response);

		$filters = $filters->initialize('admin/foo/bar');

		$filters->enableFilter('role', 'before');
		$filters->enableFilter('role', 'after');

		$found = $filters->getFilters();

		$this->assertTrue(in_array('role', $found['before']));

		$response = $filters->run('admin/foo/bar', 'before');
		$this->assertEquals('Is null', $response);

		$response = $filters->run('admin/foo/bar', 'after');
		$this->assertEquals('Is null', $response->getBody());
	}

	public function testEnableNonFilter()
	{
		$this->expectException('CodeIgniter\Filters\Exceptions\FilterException');

		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => ['google' => 'CodeIgniter\Filters\fixtures\GoogleMe'],
			'globals' => [
				'before' => [],
				'after'  => [],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);

		$filters = $filters->initialize('admin/foo/bar');

		$filters->enableFilter('goggle', 'before');
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1664
	 */
	public function testMatchesURICaseInsensitively()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config  = [
			'aliases' => [
				'foo'  => '',
				'bar'  => '',
				'frak' => '',
				'baz'  => '',
			],
			'globals' => [
				'before' => [
					'foo' => ['except' => 'Admin/*'],
					'bar'
				],
				'after'  => [
					'foo' => ['except' => 'Admin/*'],
					'baz'
				],
			],
			'filters' => [
				'frak' => [
					'before' => ['Admin/*'],
					'after'  => ['Admin/*'],
				],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$expected = [
			'before' => [
				'bar',
				'frak',
			],
			'after'  => [
				'baz',
				'frak',
			],
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1907
	 */
	public function testFilterMatching()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => [
				'foo'  => '',
				'bar'  => '',
				'frak' => '',
			],
			'filters' => [
				'frak' => [
					'before' => ['admin*'],
					'after'  => ['admin/*'],
				],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin';

		$expected = [
			'before' => [
				'frak',
			],
			'after'  => [],
		];

		$actual = $filters->initialize($uri)->getFilters();
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1907
	 */
	public function testGlobalFilterMatching()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => [
				'foo' => '',
				'one' => '',
				'two' => '',
			],
			'globals' => [
				'before' => [
					'foo' => ['except' => 'admin*'],
					'one',
				],
				'after'  => [
					'foo' => ['except' => 'admin/*'],
					'two',
				],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin';

		$expected = [
			'before' => [
				'one'
			],
			'after'  => [
				'foo',
				'two',
			],
		];

		$actual = $filters->initialize($uri)->getFilters();
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1907
	 */
	public function testCombinedFilterMatching()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => [
				'foo'  => '',
				'one'  => '',
				'frak' => '',
				'two'  => '',
			],
			'globals' => [
				'before' => [
					'foo' => ['except' => 'admin*'],
					'one',
				],
				'after'  => [
					'foo' => ['except' => 'admin/*'],
					'two',
				],
			],
			'filters' => [
				'frak' => [
					'before' => ['admin*'],
					'after'  => ['admin/*'],
				],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin123';

		$expected = [
			'before' => [
				'one',
				'frak',
			],
			'after'  => [
				'foo',
				'two',
			],
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1907
	 */
	public function testSegmentedFilterMatching()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$config = [
			'aliases' => [
				'foo'  => '',
				'one'  => '',
				'frak' => '',
			],
			'globals' => [
				'before' => [
					'foo' => ['except' => 'admin*'],
				],
				'after'  => [
					'foo' => ['except' => 'admin/*'],
				],
			],
			'filters' => [
				'frak' => [
					'before' => ['admin*'],
					'after'  => ['admin/*'],
				],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/123';

		$expected = [
			'before' => [
				'frak',
			],
			'after'  => [
				'frak',
			],
		];

		$this->assertEquals($expected, $filters->initialize($uri)->getFilters());
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/2831
	 */
	public function testFilterAlitasMultiple()
	{
		$config  = [
			'aliases' => [
				'multipeTest' => [
					'CodeIgniter\Filters\fixtures\Multiple1',
					'CodeIgniter\Filters\fixtures\Multiple2',
				],
			],
			'globals' => [
				'before' => [
					'multipeTest',
				],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$request = $filters->run($uri, 'before');
		$this->assertEquals('http://exampleMultipleURL.com', $request->url);
		$this->assertEquals('http://exampleMultipleCSP.com', $request->csp);
	}

	public function testFilterClass()
	{
		$config  = [
			'aliases' => [
				'multipleTest' => [
					'CodeIgniter\Filters\fixtures\Multiple1',
					'CodeIgniter\Filters\fixtures\Multiple2',
				],
			],
			'globals' => [
				'after' => [
					'multipleTest',
				],
			],
		];
		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$filters->run($uri, 'before');
		$expected = [
			'after'  => [
				'CodeIgniter\Filters\fixtures\Multiple1',
				'CodeIgniter\Filters\fixtures\Multiple2',
			],
			'before' => [],
		];
		$this->assertEquals($expected, $filters->getFiltersClass());
	}

}
