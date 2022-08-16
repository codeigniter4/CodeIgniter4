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
use CodeIgniter\Filters\Exceptions\FilterException;
use CodeIgniter\Filters\fixtures\GoogleCurious;
use CodeIgniter\Filters\fixtures\GoogleEmpty;
use CodeIgniter\Filters\fixtures\GoogleMe;
use CodeIgniter\Filters\fixtures\GoogleYou;
use CodeIgniter\Filters\fixtures\InvalidClass;
use CodeIgniter\Filters\fixtures\Multiple1;
use CodeIgniter\Filters\fixtures\Multiple2;
use CodeIgniter\Filters\fixtures\Role;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ConfigFromArrayTrait;
use CodeIgniter\Test\Mock\MockAppConfig;
use Config\Filters as FiltersConfig;

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
 *
 * @internal
 */
final class FiltersTest extends CIUnitTestCase
{
    use ConfigFromArrayTrait;

    private $response;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices();

        $defaults = [
            'Config'        => APPPATH . 'Config',
            'App'           => APPPATH,
            'Tests\Support' => TESTPATH . '_support',
        ];
        Services::autoloader()->addNamespace($defaults);

        $_SERVER = [];

        $this->response = Services::response();
    }

    private function createFilters(FiltersConfig $config, $request = null): Filters
    {
        $request ??= Services::request();

        return new Filters($config, $request, $this->response);
    }

    public function testProcessMethodDetectsCLI()
    {
        $_SERVER['argv'] = [
            'spark',
            'list',
        ];
        $_SERVER['argc'] = 2;

        $config = [
            'aliases' => ['foo' => ''],
            'globals' => [],
            'methods' => [
                'cli' => ['foo'],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters(
            $filtersConfig,
            new CLIRequest(new MockAppConfig())
        );

        $expected = [
            'before' => ['foo'],
            'after'  => [],
        ];
        $this->assertSame($expected, $filters->initialize()->getFilters());
    }

    public function testProcessMethodDetectsGetRequests()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => ['foo' => ''],
            'globals' => [],
            'methods' => [
                'get' => ['foo'],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $expected = [
            'before' => ['foo'],
            'after'  => [],
        ];
        $this->assertSame($expected, $filters->initialize()->getFilters());
    }

    public function testProcessMethodRespectsMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => [
                'foo' => '',
                'bar' => '',
            ],
            'globals' => [],
            'methods' => [
                'post' => ['foo'],
                'get'  => ['bar'],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $expected = [
            'before' => ['bar'],
            'after'  => [],
        ];
        $this->assertSame($expected, $filters->initialize()->getFilters());
    }

    public function testProcessMethodIgnoresMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        $config = [
            'aliases' => [
                'foo' => '',
                'bar' => '',
            ],
            'globals' => [],
            'methods' => [
                'post' => ['foo'],
                'get'  => ['bar'],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $expected = [
            'before' => [],
            'after'  => [],
        ];
        $this->assertSame($expected, $filters->initialize()->getFilters());
    }

    public function testProcessMethodProcessGlobals()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => [
                'foo' => '',
                'bar' => '',
                'baz' => '',
            ],
            'globals' => [
                'before' => [
                    'foo' => ['bar'], // not excluded
                    'bar',
                ],
                'after' => [
                    'baz',
                ],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $expected = [
            'before' => [
                'foo',
                'bar',
            ],
            'after' => ['baz'],
        ];
        $this->assertSame($expected, $filters->initialize()->getFilters());
    }

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

        $config = [
            'aliases' => [
                'foo' => '',
                'bar' => '',
                'baz' => '',
            ],
            'globals' => [
                'before' => [
                    'foo' => ['except' => $except],
                    'bar',
                ],
                'after' => [
                    'baz',
                ],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'admin/foo/bar';
        $expected = [
            'before' => [
                'bar',
            ],
            'after' => ['baz'],
        ];
        $this->assertSame($expected, $filters->initialize($uri)->getFilters());
    }

    public function testProcessMethodProcessesFiltersBefore()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => [
                'foo' => '',
                'bar' => '',
                'baz' => '',
            ],
            'globals' => [],
            'filters' => [
                'foo' => [
                    'before' => ['admin/*'],
                    'after'  => ['/users/*'],
                ],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'admin/foo/bar';
        $expected = [
            'before' => ['foo'],
            'after'  => [],
        ];
        $this->assertSame($expected, $filters->initialize($uri)->getFilters());
    }

    public function testProcessMethodProcessesFiltersAfter()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => [
                'foo' => '',
                'bar' => '',
                'baz' => '',
            ],
            'globals' => [],
            'filters' => [
                'foo' => [
                    'before' => ['admin/*'],
                    'after'  => ['/users/*'],
                ],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'users/foo/bar';
        $expected = [
            'before' => [],
            'after'  => [
                'foo',
            ],
        ];
        $this->assertSame($expected, $filters->initialize($uri)->getFilters());
    }

    public function testProcessMethodProcessesCombined()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
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
                    'barg',
                ],
                'after' => [
                    'bazg',
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
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'admin/foo/bar';
        $expected = [
            'before' => [
                'barg',
                'bar',
                'foof',
            ],
            'after' => ['bazg'],
        ];
        $this->assertSame($expected, $filters->initialize($uri)->getFilters());
    }

    public function testProcessMethodProcessesCombinedAfterForToolbar()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
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
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'admin/foo/bar';
        $expected = [
            'before' => ['bar'],
            'after'  => [
                'bazg',
                'foof',
                'toolbar',
            ],
        ];
        $this->assertSame($expected, $filters->initialize($uri)->getFilters());
    }

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
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $this->expectException(FilterException::class);

        $uri = 'admin/foo/bar';
        $filters->run($uri);
    }

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
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri     = 'admin/foo/bar';
        $request = $filters->run($uri, 'before');

        $this->assertSame('http://hellowworld.com', $request->getBody());

        $this->resetServices();
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4720
     */
    public function testAllCustomFiltersAreDiscoveredInConstructor()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => [],
            'globals' => [],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $configFilters = $this->getPrivateProperty($filters, 'config');
        $this->assertContains('test-customfilter', array_keys($configFilters->aliases));
    }

    public function testRunThrowsWithInvalidClassType()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => ['invalid' => InvalidClass::class],
            'globals' => [
                'before' => ['invalid'],
                'after'  => [],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $this->expectException(FilterException::class);

        $uri = 'admin/foo/bar';
        $filters->run($uri);
    }

    public function testRunDoesBefore()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => ['google' => GoogleMe::class],
            'globals' => [
                'before' => ['google'],
                'after'  => [],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri     = 'admin/foo/bar';
        $request = $filters->run($uri, 'before');

        $this->assertSame('http://google.com', $request->url);
    }

    public function testRunDoesAfter()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => ['google' => GoogleMe::class],
            'globals' => [
                'before' => [],
                'after'  => ['google'],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'admin/foo/bar';
        $response = $filters->run($uri, 'after');

        $this->assertSame('http://google.com', $response->csp);
    }

    public function testShortCircuit()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => ['banana' => GoogleYou::class],
            'globals' => [
                'before' => ['banana'],
                'after'  => [],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'admin/foo/bar';
        $response = $filters->run($uri, 'before');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('http://google.com', $response->csp);
    }

    public function testOtherResult()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => [
                'nowhere' => GoogleEmpty::class,
                'banana'  => GoogleCurious::class,
            ],
            'globals' => [
                'before' => [
                    'nowhere',
                    'banana',
                ],
                'after' => [],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'admin/foo/bar';
        $response = $filters->run($uri, 'before');

        $this->assertSame('This is curious', $response);
    }

    public function testBeforeExceptString()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => [
                'foo' => '',
                'bar' => '',
                'baz' => '',
            ],
            'globals' => [
                'before' => [
                    'foo' => ['except' => 'admin/*'],
                    'bar',
                ],
                'after' => [
                    'baz',
                ],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'admin/foo/bar';
        $expected = [
            'before' => [
                'bar',
            ],
            'after' => ['baz'],
        ];
        $this->assertSame($expected, $filters->initialize($uri)->getFilters());
    }

    public function testBeforeExceptInapplicable()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => [
                'foo' => '',
                'bar' => '',
                'baz' => '',
            ],
            'globals' => [
                'before' => [
                    'foo' => ['except' => 'george/*'],
                    'bar',
                ],
                'after' => [
                    'baz',
                ],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'admin/foo/bar';
        $expected = [
            'before' => [
                'foo',
                'bar',
            ],
            'after' => ['baz'],
        ];
        $this->assertSame($expected, $filters->initialize($uri)->getFilters());
    }

    public function testAfterExceptString()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => [
                'foo' => '',
                'bar' => '',
                'baz' => '',
            ],
            'globals' => [
                'before' => [
                    'bar',
                ],
                'after' => [
                    'foo' => ['except' => 'admin/*'],
                    'baz',
                ],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'admin/foo/bar';
        $expected = [
            'before' => [
                'bar',
            ],
            'after' => ['baz'],
        ];
        $this->assertSame($expected, $filters->initialize($uri)->getFilters());
    }

    public function testAfterExceptInapplicable()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => [
                'foo' => '',
                'bar' => '',
                'baz' => '',
            ],
            'globals' => [
                'before' => [
                    'bar',
                ],
                'after' => [
                    'foo' => ['except' => 'george/*'],
                    'baz',
                ],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'admin/foo/bar';
        $expected = [
            'before' => [
                'bar',
            ],
            'after' => [
                'foo',
                'baz',
            ],
        ];
        $this->assertSame($expected, $filters->initialize($uri)->getFilters());
    }

    public function testAddFilter()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => ['google' => GoogleMe::class],
            'globals' => [
                'before' => ['google'],
                'after'  => [],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $filters = $filters->addFilter('Some\Class', 'some_alias');
        $filters = $filters->initialize('admin/foo/bar');
        $filters = $filters->getFilters();

        $this->assertContains('some_alias', $filters['before']);
    }

    public function testAddFilterSection()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config        = [];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $list = $filters
            ->addFilter('Some\OtherClass', 'another', 'before', 'globals')
            ->initialize('admin/foo/bar')
            ->getFilters();

        $this->assertContains('another', $list['before']);
    }

    public function testInitializeTwice()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config        = [];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $list = $filters
            ->addFilter('Some\OtherClass', 'another', 'before', 'globals')
            ->initialize('admin/foo/bar')
            ->initialize()
            ->getFilters();

        $this->assertContains('another', $list['before']);
    }

    public function testEnableFilter()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => ['google' => GoogleMe::class],
            'globals' => [
                'before' => [],
                'after'  => [],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $filters = $filters->initialize('admin/foo/bar');
        $filters->enableFilter('google', 'before');
        $filters = $filters->getFilters();

        $this->assertContains('google', $filters['before']);
    }

    public function testEnableFilterWithArguments()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => ['role' => Role::class],
            'globals' => [
                'before' => [],
                'after'  => [],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $filters = $filters->initialize('admin/foo/bar');
        $filters->enableFilter('role:admin , super', 'before');
        $filters->enableFilter('role:admin , super', 'after');
        $found = $filters->getFilters();

        $this->assertContains('role', $found['before']);
        $this->assertSame(['admin', 'super'], $filters->getArguments('role'));
        $this->assertSame(['role' => ['admin', 'super']], $filters->getArguments());

        $response = $filters->run('admin/foo/bar', 'before');

        $this->assertSame('admin;super', $response);

        $response = $filters->run('admin/foo/bar', 'after');

        $this->assertSame('admin;super', $response->getBody());
    }

    public function testEnableFilterWithNoArguments()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => ['role' => Role::class],
            'globals' => [
                'before' => [],
                'after'  => [],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $filters = $filters->initialize('admin/foo/bar');
        $filters->enableFilter('role', 'before');
        $filters->enableFilter('role', 'after');
        $found = $filters->getFilters();

        $this->assertContains('role', $found['before']);

        $response = $filters->run('admin/foo/bar', 'before');

        $this->assertSame('Is null', $response);

        $response = $filters->run('admin/foo/bar', 'after');

        $this->assertSame('Is null', $response->getBody());
    }

    public function testEnableNonFilter()
    {
        $this->expectException(FilterException::class);

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => ['google' => GoogleMe::class],
            'globals' => [
                'before' => [],
                'after'  => [],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $filters = $filters->initialize('admin/foo/bar');
        $filters->enableFilter('goggle', 'before');
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1664
     */
    public function testMatchesURICaseInsensitively()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => [
                'foo'  => '',
                'bar'  => '',
                'frak' => '',
                'baz'  => '',
            ],
            'globals' => [
                'before' => [
                    'foo' => ['except' => 'Admin/*'],
                    'bar',
                ],
                'after' => [
                    'foo' => ['except' => 'Admin/*'],
                    'baz',
                ],
            ],
            'filters' => [
                'frak' => [
                    'before' => ['Admin/*'],
                    'after'  => ['Admin/*'],
                ],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'admin/foo/bar';
        $expected = [
            'before' => [
                'bar',
                'frak',
            ],
            'after' => [
                'baz',
                'frak',
            ],
        ];
        $this->assertSame($expected, $filters->initialize($uri)->getFilters());
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
            'globals' => [],
            'filters' => [
                'frak' => [
                    'before' => ['admin*'],
                    'after'  => ['admin/*'],
                ],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri    = 'admin';
        $actual = $filters->initialize($uri)->getFilters();

        $expected = [
            'before' => [
                'frak',
            ],
            'after' => [],
        ];
        $this->assertSame($expected, $actual);
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
                'after' => [
                    'foo' => ['except' => 'admin/*'],
                    'two',
                ],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri    = 'admin';
        $actual = $filters->initialize($uri)->getFilters();

        $expected = [
            'before' => [
                'one',
            ],
            'after' => [
                'foo',
                'two',
            ],
        ];
        $this->assertSame($expected, $actual);
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
                'after' => [
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
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'admin123';
        $expected = [
            'before' => [
                'one',
                'frak',
            ],
            'after' => [
                'foo',
                'two',
            ],
        ];
        $this->assertSame($expected, $filters->initialize($uri)->getFilters());
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
                'after' => [
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
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri      = 'admin/123';
        $expected = [
            'before' => [
                'frak',
            ],
            'after' => [
                'frak',
            ],
        ];
        $this->assertSame($expected, $filters->initialize($uri)->getFilters());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2831
     */
    public function testFilterAliasMultiple()
    {
        $config = [
            'aliases' => [
                'multipleTest' => [
                    Multiple1::class,
                    Multiple2::class,
                ],
            ],
            'globals' => [
                'before' => [
                    'multipleTest',
                ],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri     = 'admin/foo/bar';
        $request = $filters->run($uri, 'before');

        $this->assertSame('http://exampleMultipleURL.com', $request->url);
        $this->assertSame('http://exampleMultipleCSP.com', $request->csp);
    }

    public function testFilterClass()
    {
        $config = [
            'aliases' => [
                'multipleTest' => [
                    Multiple1::class,
                    Multiple2::class,
                ],
            ],
            'globals' => [
                'after' => [
                    'multipleTest',
                ],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $filters->run('admin/foo/bar', 'before');

        $expected = [
            'before' => [],
            'after'  => [
                Multiple1::class,
                Multiple2::class,
            ],
        ];
        $this->assertSame($expected, $filters->getFiltersClass());
    }

    public function testReset()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'aliases' => [
                'foo' => '',
            ],
            'globals' => [],
            'filters' => [
                'foo' => [
                    'before' => ['admin*'],
                ],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);
        $filters       = $this->createFilters($filtersConfig);

        $uri = 'admin';
        $this->assertSame(['foo'], $filters->initialize($uri)->getFilters()['before']);
        $this->assertSame([], $filters->reset()->getFilters()['before']);
    }
}
