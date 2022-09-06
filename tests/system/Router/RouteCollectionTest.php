<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Router;

use App\Controllers\Home;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Modules;
use Tests\Support\Controllers\Hello;

/**
 * @internal
 */
final class RouteCollectionTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices(true);
        $this->resetFactories();
    }

    protected function getCollector(array $config = [], array $files = [], $moduleConfig = null)
    {
        $defaults = [
            'Config' => APPPATH . 'Config',
            'App'    => APPPATH,
        ];
        $config = array_merge($config, $defaults);

        Services::autoloader()->addNamespace($config);

        $loader = Services::locator();

        if ($moduleConfig === null) {
            $moduleConfig          = new Modules();
            $moduleConfig->enabled = false;
        }

        return (new RouteCollection($loader, $moduleConfig))->setHTTPVerb('get');
    }

    public function testBasicAdd()
    {
        $routes = $this->getCollector();

        $routes->add('home', '\my\controller');

        $expects = [
            'home' => '\my\controller',
        ];

        $routes = $routes->getRoutes();

        $this->assertSame($expects, $routes);
    }

    public function testBasicAddCallable()
    {
        $routes = $this->getCollector();

        $routes->add('home', [Hello::class, 'index']);

        $routes  = $routes->getRoutes();
        $expects = [
            'home' => '\Tests\Support\Controllers\Hello::index',
        ];
        $this->assertSame($expects, $routes);
    }

    public function testBasicAddCallableWithParamsString()
    {
        $routes = $this->getCollector();

        $routes->add('product/(:num)/(:num)', [[Hello::class, 'index'], '$2/$1']);

        $routes  = $routes->getRoutes();
        $expects = [
            'product/([0-9]+)/([0-9]+)' => '\Tests\Support\Controllers\Hello::index/$2/$1',
        ];
        $this->assertSame($expects, $routes);
    }

    public function testBasicAddCallableWithParamsWithoutString()
    {
        $routes = $this->getCollector();

        $routes->add('product/(:num)/(:num)', [Hello::class, 'index']);

        $routes  = $routes->getRoutes();
        $expects = [
            'product/([0-9]+)/([0-9]+)' => '\Tests\Support\Controllers\Hello::index/$1/$2',
        ];
        $this->assertSame($expects, $routes);
    }

    public function testAddPrefixesDefaultNamespaceWhenNoneExist()
    {
        $routes = $this->getCollector();

        $routes->add('home', 'controller');

        $expects = [
            'home' => '\controller',
        ];

        $routes = $routes->getRoutes();

        $this->assertSame($expects, $routes);
    }

    public function testAddIgnoresDefaultNamespaceWhenExists()
    {
        $routes = $this->getCollector();

        $routes->add('home', 'my\controller');

        $expects = [
            'home' => '\my\controller',
        ];

        $routes = $routes->getRoutes();

        $this->assertSame($expects, $routes);
    }

    public function testAddWorksWithCurrentHTTPMethods()
    {
        Services::request()->setMethod('get');

        $routes = $this->getCollector();

        $routes->match(['get'], 'home', 'controller');

        $expects = [
            'home' => '\controller',
        ];

        $routes = $routes->getRoutes();

        $this->assertSame($expects, $routes);
    }

    public function testAddWithLeadingSlash()
    {
        $routes = $this->getCollector();

        $routes->add('/home', 'controller');

        $expects = [
            'home' => '\controller',
        ];

        $routes = $routes->getRoutes();

        $this->assertSame($expects, $routes);
    }

    public function testMatchIgnoresInvalidHTTPMethods()
    {
        Services::request()->setMethod('get');

        $routes = $this->getCollector();

        $routes->match(['put'], 'home', 'controller');

        $routes = $routes->getRoutes();

        $this->assertSame([], $routes);
    }

    public function testAddWorksWithArrayOFHTTPMethods()
    {
        Services::request()->setMethod('post');

        $routes = $this->getCollector();

        $routes->add('home', 'controller', ['get', 'post']);

        $expects = [
            'home' => '\controller',
        ];

        $routes = $routes->getRoutes();

        $this->assertSame($expects, $routes);
    }

    public function testAddReplacesDefaultPlaceholders()
    {
        $routes = $this->getCollector();

        $routes->add('home/(:any)', 'controller');

        $expects = [
            'home/(.*)' => '\controller',
        ];

        $routes = $routes->getRoutes();

        $this->assertSame($expects, $routes);
    }

    public function testAddReplacesCustomPlaceholders()
    {
        $routes = $this->getCollector();
        $routes->addPlaceholder('smiley', ':-)');

        $routes->add('home/(:smiley)', 'controller');

        $expects = [
            'home/(:-))' => '\controller',
        ];

        $routes = $routes->getRoutes();

        $this->assertSame($expects, $routes);
    }

    public function testAddRecognizesCustomNamespaces()
    {
        $routes = $this->getCollector();
        $routes->setDefaultNamespace('\CodeIgniter');

        $routes->add('home', 'controller');

        $expects = [
            'home' => '\CodeIgniter\controller',
        ];

        $routes = $routes->getRoutes();

        $this->assertSame($expects, $routes);
    }

    public function testSetDefaultControllerStoresIt()
    {
        $routes = $this->getCollector();
        $routes->setDefaultController('godzilla');

        $this->assertSame('godzilla', $routes->getDefaultController());
    }

    public function testSetDefaultMethodStoresIt()
    {
        $routes = $this->getCollector();
        $routes->setDefaultMethod('biggerBox');

        $this->assertSame('biggerBox', $routes->getDefaultMethod());
    }

    public function testTranslateURIDashesWorks()
    {
        $routes = $this->getCollector();
        $routes->setTranslateURIDashes(true);

        $this->assertTrue($routes->shouldTranslateURIDashes());
    }

    public function testAutoRouteStoresIt()
    {
        $routes = $this->getCollector();
        $routes->setAutoRoute(true);

        $this->assertTrue($routes->shouldAutoRoute());
    }

    public function testGroupingWorks()
    {
        $routes = $this->getCollector();

        $routes->group(
            'admin',
            static function ($routes) {
                $routes->add('users/list', '\Users::list');
            }
        );

        $expected = [
            'admin/users/list' => '\Users::list',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testGroupGetsSanitized()
    {
        $routes = $this->getCollector();

        $routes->group(
            '<script>admin',
            static function ($routes) {
                $routes->add('users/list', '\Users::list');
            }
        );

        $expected = [
            'admin/users/list' => '\Users::list',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testGroupSetsOptions()
    {
        $routes = $this->getCollector();

        $routes->group(
            'admin',
            ['namespace' => 'Admin'],
            static function ($routes) {
                $routes->add('users/list', 'Users::list');
            }
        );

        $expected = [
            'admin/users/list' => '\Admin\Users::list',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testGroupingWorksWithEmptyStringPrefix()
    {
        $routes = $this->getCollector();

        $routes->group(
            '',
            static function ($routes) {
                $routes->add('users/list', '\Users::list');
            }
        );

        $expected = [
            'users/list' => '\Users::list',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testNestedGroupingWorksWithEmptyPrefix()
    {
        $routes = $this->getCollector();

        $routes->add('verify/begin', '\VerifyController::begin');

        $routes->group('admin', static function ($routes) {
            $routes->group(
                '',
                static function ($routes) {
                    $routes->add('users/list', '\Users::list');

                    $routes->group('delegate', static function ($routes) {
                        $routes->add('foo', '\Users::foo');
                    });
                }
            );
        });

        $expected = [
            'verify/begin'       => '\VerifyController::begin',
            'admin/users/list'   => '\Users::list',
            'admin/delegate/foo' => '\Users::foo',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    /**
     * @dataProvider groupProvider
     */
    public function testNestedGroupingWorksWithRootPrefix(
        string $group,
        string $subgroup,
        array $expected
    ) {
        $routes = $this->getCollector();

        $routes->group($group, static function ($routes) use ($subgroup) {
            $routes->group(
                $subgroup,
                static function ($routes) {
                    $routes->add('users/list', '\Users::list');

                    $routes->group('delegate', static function ($routes) {
                        $routes->add('foo', '\Users::foo');
                    });
                }
            );
        });

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function groupProvider()
    {
        yield from [
            ['admin', '/', [
                'admin/users/list'   => '\Users::list',
                'admin/delegate/foo' => '\Users::foo',
            ]],
            ['/', '', [
                'users/list'   => '\Users::list',
                'delegate/foo' => '\Users::foo',
            ]],
            ['', '', [
                'users/list'   => '\Users::list',
                'delegate/foo' => '\Users::foo',
            ]],
            ['', '/', [
                'users/list'   => '\Users::list',
                'delegate/foo' => '\Users::foo',
            ]],
        ];
    }

    public function testHostnameOption()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $routes = $this->getCollector();

        $routes->add('from', 'to', ['hostname' => 'example.com']);
        $routes->add('foo', 'bar', ['hostname' => 'foobar.com']);

        $expected = [
            'from' => '\to',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testResourceScaffoldsCorrectly()
    {
        $routes = $this->getCollector();
        $routes->setHTTPVerb('get');

        $routes->resource('photos');

        $expected = [
            'photos'           => '\Photos::index',
            'photos/new'       => '\Photos::new',
            'photos/(.*)/edit' => '\Photos::edit/$1',
            'photos/(.*)'      => '\Photos::show/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());

        $routes = $this->getCollector();
        $routes->setHTTPVerb('post');
        $routes->resource('photos');

        $expected = [
            'photos' => '\Photos::create',
        ];

        $this->assertSame($expected, $routes->getRoutes());

        $routes = $this->getCollector();
        $routes->setHTTPVerb('put');
        $routes->resource('photos');

        $expected = [
            'photos/(.*)' => '\Photos::update/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());

        $routes = $this->getCollector();
        $routes->setHTTPVerb('patch');
        $routes->resource('photos');

        $expected = [
            'photos/(.*)' => '\Photos::update/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());

        $routes = $this->getCollector();
        $routes->setHTTPVerb('delete');
        $routes->resource('photos');

        $expected = [
            'photos/(.*)' => '\Photos::delete/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    // Similar to the above, but with a more typical endpoint

    public function testResourceAPIScaffoldsCorrectly()
    {
        $routes = $this->getCollector();
        $routes->setHTTPVerb('get');

        $routes->resource('api/photos', ['controller' => 'Photos']);

        $expected = [
            'api/photos'           => '\Photos::index',
            'api/photos/new'       => '\Photos::new',
            'api/photos/(.*)/edit' => '\Photos::edit/$1',
            'api/photos/(.*)'      => '\Photos::show/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());

        $routes = $this->getCollector();
        $routes->setHTTPVerb('post');
        $routes->resource('api/photos', ['controller' => 'Photos']);

        $expected = [
            'api/photos' => '\Photos::create',
        ];

        $this->assertSame($expected, $routes->getRoutes());

        $routes = $this->getCollector();
        $routes->setHTTPVerb('put');
        $routes->resource('api/photos', ['controller' => 'Photos']);

        $expected = [
            'api/photos/(.*)' => '\Photos::update/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());

        $routes = $this->getCollector();
        $routes->setHTTPVerb('patch');
        $routes->resource('api/photos', ['controller' => 'Photos']);

        $expected = [
            'api/photos/(.*)' => '\Photos::update/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());

        $routes = $this->getCollector();
        $routes->setHTTPVerb('delete');
        $routes->resource('api/photos', ['controller' => 'Photos']);

        $expected = [
            'api/photos/(.*)' => '\Photos::delete/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testPresenterScaffoldsCorrectly()
    {
        $routes = $this->getCollector();
        $routes->setHTTPVerb('get');

        $routes->presenter('photos');

        $expected = [
            'photos'             => '\Photos::index',
            'photos/show/(.*)'   => '\Photos::show/$1',
            'photos/new'         => '\Photos::new',
            'photos/edit/(.*)'   => '\Photos::edit/$1',
            'photos/remove/(.*)' => '\Photos::remove/$1',
            'photos/(.*)'        => '\Photos::show/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());

        $routes = $this->getCollector();
        $routes->setHTTPVerb('post');
        $routes->presenter('photos');

        $expected = [
            'photos/create'      => '\Photos::create',
            'photos/update/(.*)' => '\Photos::update/$1',
            'photos/delete/(.*)' => '\Photos::delete/$1',
            'photos'             => '\Photos::create',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testResourcesWithCustomController()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $routes->resource('photos', ['controller' => '<script>gallery']);

        $expected = [
            'photos'           => '\Gallery::index',
            'photos/new'       => '\Gallery::new',
            'photos/(.*)/edit' => '\Gallery::edit/$1',
            'photos/(.*)'      => '\Gallery::show/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testResourcesWithCustomPlaceholder()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $routes->resource('photos', ['placeholder' => ':num']);

        $expected = [
            'photos'               => '\Photos::index',
            'photos/new'           => '\Photos::new',
            'photos/([0-9]+)/edit' => '\Photos::edit/$1',
            'photos/([0-9]+)'      => '\Photos::show/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testResourcesWithDefaultPlaceholder()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $routes->setDefaultConstraint('num');
        $routes->resource('photos');

        $expected = [
            'photos'               => '\Photos::index',
            'photos/new'           => '\Photos::new',
            'photos/([0-9]+)/edit' => '\Photos::edit/$1',
            'photos/([0-9]+)'      => '\Photos::show/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testResourcesWithBogusDefaultPlaceholder()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $routes->setDefaultConstraint(':num');
        $routes->resource('photos');

        $expected = [
            'photos'           => '\Photos::index',
            'photos/new'       => '\Photos::new',
            'photos/(.*)/edit' => '\Photos::edit/$1',
            'photos/(.*)'      => '\Photos::show/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testResourcesWithOnly()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $routes->resource('photos', ['only' => 'index']);

        $expected = [
            'photos' => '\Photos::index',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testResourcesWithExcept()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $routes->resource('photos', ['except' => 'edit,new']);

        $expected = [
            'photos'      => '\Photos::index',
            'photos/(.*)' => '\Photos::show/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testResourcesWithWebsafe()
    {
        $routes = $this->getCollector();
        $routes->setHTTPVerb('post');

        $routes->resource('photos', ['websafe' => true]);

        $expected = [
            'photos'             => '\Photos::create',
            'photos/(.*)/delete' => '\Photos::delete/$1',
            'photos/(.*)'        => '\Photos::update/$1',
        ];

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testMatchSupportsMultipleMethods()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $expected = ['here' => '\there'];

        $routes->match(['get', 'post'], 'here', 'there');
        $this->assertSame($expected, $routes->getRoutes());

        Services::request()->setMethod('post');
        $routes = $this->getCollector();
        $routes->match(['get', 'post'], 'here', 'there');
        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testGet()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $expected = ['here' => '\there'];

        $routes->get('here', 'there');
        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testPost()
    {
        $routes = $this->getCollector();
        $routes->setHTTPVerb('post');

        $expected = ['here' => '\there'];

        $routes->post('here', 'there');
        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testGetDoesntAllowOtherMethods()
    {
        $routes = $this->getCollector();
        $routes->setHTTPVerb('get');

        $expected = ['here' => '\there'];

        $routes->get('here', 'there');
        $routes->post('from', 'to');
        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testPut()
    {
        $routes = $this->getCollector();
        $routes->setHTTPVerb('put');

        $expected = ['here' => '\there'];

        $routes->put('here', 'there');
        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testDelete()
    {
        $routes = $this->getCollector();
        $routes->setHTTPVerb('delete');

        $expected = ['here' => '\there'];

        $routes->delete('here', 'there');
        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testHead()
    {
        $routes = $this->getCollector();
        $routes->setHTTPVerb('head');

        $expected = ['here' => '\there'];

        $routes->head('here', 'there');
        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testPatch()
    {
        $routes = $this->getCollector();
        $routes->setHTTPVerb('patch');

        $expected = ['here' => '\there'];

        $routes->patch('here', 'there');
        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testOptions()
    {
        $routes = $this->getCollector();
        $routes->setHTTPVerb('options');

        $expected = ['here' => '\there'];

        $routes->options('here', 'there');
        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testCLI()
    {
        $routes = $this->getCollector();

        $expected = ['here' => '\there'];

        $routes->cli('here', 'there');
        $this->assertSame($expected, $routes->getRoutes('cli'));
    }

    public function testEnvironmentRestricts()
    {
        // ENVIRONMENT should be 'testing'

        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $expected = ['here' => '\there'];

        $routes->environment(
            'testing',
            static function ($routes) {
                $routes->get('here', 'there');
            }
        );

        $routes->environment(
            'badenvironment',
            static function ($routes) {
                $routes->get('from', 'to');
            }
        );

        $this->assertSame($expected, $routes->getRoutes());
    }

    public function testNamedRoutes()
    {
        $routes = $this->getCollector();

        $routes->add('users', 'Users::index', ['as' => 'namedRoute']);

        $this->assertSame('/users', $routes->reverseRoute('namedRoute'));
    }

    public function testNamedRoutesWithLocale()
    {
        $routes = $this->getCollector();

        $routes->add('{locale}/users', 'Users::index', ['as' => 'namedRoute']);

        $this->assertSame('/en/users', $routes->reverseRoute('namedRoute'));
    }

    public function testNamedRoutesFillInParams()
    {
        $routes = $this->getCollector();

        $routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2', ['as' => 'namedRoute']);

        $match = $routes->reverseRoute('namedRoute', 'string', 13);

        $this->assertSame('/path/string/to/13', $match);
    }

    public function testNamedRoutesWithLocaleAndFillInParams()
    {
        $routes = $this->getCollector();

        $routes->add('{locale}/path/(:any)/to/(:num)', 'myController::goto/$1/$2', ['as' => 'namedRoute']);

        $match = $routes->reverseRoute('namedRoute', 'string', 13);

        $this->assertSame('/en/path/string/to/13', $match);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/642
     */
    public function testNamedRoutesWithSameURIDifferentMethods()
    {
        $routes = $this->getCollector();

        $routes->get('user/insert', 'myController::goto/$1/$2', ['as' => 'namedRoute1']);
        $routes->post(
            'user/insert',
            static function () {},
            ['as' => 'namedRoute2']
        );
        $routes->put(
            'user/insert',
            static function () {},
            ['as' => 'namedRoute3']
        );

        $match1 = $routes->reverseRoute('namedRoute1');
        $match2 = $routes->reverseRoute('namedRoute2');
        $match3 = $routes->reverseRoute('namedRoute3');

        $this->assertSame('/user/insert', $match1);
        $this->assertSame('/user/insert', $match2);
        $this->assertSame('/user/insert', $match3);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/642
     */
    public function testNamedRoutesWithLocaleAndWithSameURIDifferentMethods()
    {
        $routes = $this->getCollector();

        $routes->get('{locale}/user/insert', 'myController::goto/$1/$2', ['as' => 'namedRoute1']);
        $routes->post(
            '{locale}/user/insert',
            static function () {
            },
            ['as' => 'namedRoute2']
        );
        $routes->put(
            '{locale}/user/insert',
            static function () {
            },
            ['as' => 'namedRoute3']
        );

        $match1 = $routes->reverseRoute('namedRoute1');
        $match2 = $routes->reverseRoute('namedRoute2');
        $match3 = $routes->reverseRoute('namedRoute3');

        $this->assertSame('/en/user/insert', $match1);
        $this->assertSame('/en/user/insert', $match2);
        $this->assertSame('/en/user/insert', $match3);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3048
     */
    public function testNamedRoutesWithPipesInRegex()
    {
        $routes = $this->getCollector();

        $routes->get('/system/(this|that)', 'myController::system/$1', ['as' => 'pipedRoute']);

        $this->assertSame('/system/this', $routes->reverseRoute('pipedRoute', 'this'));
        $this->assertSame('/system/that', $routes->reverseRoute('pipedRoute', 'that'));
    }

    public function testAddRedirect()
    {
        $routes = $this->getCollector();

        // The second parameter is either the new URI to redirect to, or the name of a named route.
        $routes->addRedirect('users', 'users/index', 307);

        $expected = [
            'users' => 'users/index',
        ];

        $this->assertSame($expected, $routes->getRoutes());
        $this->assertTrue($routes->isRedirect('users'));
        $this->assertSame(307, $routes->getRedirectCode('users'));
        $this->assertSame(0, $routes->getRedirectCode('bosses'));
    }

    public function testAddRedirectNamed()
    {
        $routes = $this->getCollector();

        $routes->add('zombies', 'Zombies::index', ['as' => 'namedRoute']);
        $routes->addRedirect('users', 'namedRoute', 307);

        $expected = [
            'zombies' => '\Zombies::index',
            'users'   => ['zombies' => '\Zombies::index'],
        ];

        $this->assertSame($expected, $routes->getRoutes());
        $this->assertTrue($routes->isRedirect('users'));
        $this->assertSame(307, $routes->getRedirectCode('users'));
    }

    public function testAddRedirectGetMethod()
    {
        $routes = $this->getCollector();

        $routes->get('zombies', 'Zombies::index', ['as' => 'namedRoute']);
        $routes->addRedirect('users', 'namedRoute', 307);

        $expected = [
            'zombies' => '\Zombies::index',
            'users'   => ['zombies' => '\Zombies::index'],
        ];

        $this->assertSame($expected, $routes->getRoutes());
        $this->assertTrue($routes->isRedirect('users'));
        $this->assertSame(307, $routes->getRedirectCode('users'));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/497
     */
    public function testWithSubdomain()
    {
        $_SERVER['HTTP_HOST'] = 'adm.example.com';

        $routes = $this->getCollector();

        $routes->add('/objects/(:alphanum)', 'Admin::objectsList/$1', ['subdomain' => 'adm']);
        $routes->add('/objects/(:alphanum)', 'App::objectsList/$1');

        $expects = [
            'objects/([a-zA-Z0-9]+)' => '\Admin::objectsList/$1',
        ];
        $this->assertSame($expects, $routes->getRoutes());
    }

    public function testWithSubdomainMissing()
    {
        $_SERVER['HTTP_HOST'] = 'www.example.com';

        $routes = $this->getCollector();

        $routes->add('/objects/(:alphanum)', 'Admin::objectsList/$1', ['subdomain' => 'adm']);
        $routes->add('/objects/(:alphanum)', 'App::objectsList/$1');

        $expects = [
            'objects/([a-zA-Z0-9]+)' => '\App::objectsList/$1',
        ];
        $this->assertSame($expects, $routes->getRoutes());
    }

    public function testWithDifferentSubdomain()
    {
        $_SERVER['HTTP_HOST'] = 'adm.example.com';

        $routes = $this->getCollector();

        $routes->add('/objects/(:alphanum)', 'Admin::objectsList/$1', ['subdomain' => 'sales']);
        $routes->add('/objects/(:alphanum)', 'App::objectsList/$1');

        $expects = [
            'objects/([a-zA-Z0-9]+)' => '\App::objectsList/$1',
        ];
        $this->assertSame($expects, $routes->getRoutes());
    }

    public function testWithWWWSubdomain()
    {
        $routes = $this->getCollector();

        $_SERVER['HTTP_HOST'] = 'www.example.com';

        $routes->add('/objects/(:alphanum)', 'Admin::objectsList/$1', ['subdomain' => 'sales']);
        $routes->add('/objects/(:alphanum)', 'App::objectsList/$1');

        $expects = [
            'objects/([a-zA-Z0-9]+)' => '\App::objectsList/$1',
        ];

        $this->assertSame($expects, $routes->getRoutes());
    }

    public function testWithDotCoSubdomain()
    {
        $routes = $this->getCollector();

        $_SERVER['HTTP_HOST'] = 'example.co.uk';

        $routes->add('/objects/(:alphanum)', 'Admin::objectsList/$1', ['subdomain' => 'sales']);
        $routes->add('/objects/(:alphanum)', 'App::objectsList/$1');

        $expects = [
            'objects/([a-zA-Z0-9]+)' => '\App::objectsList/$1',
        ];

        $this->assertSame($expects, $routes->getRoutes());
    }

    public function testWithDifferentSubdomainMissing()
    {
        $_SERVER['HTTP_HOST'] = 'adm.example.com';

        $routes = $this->getCollector();

        $routes->add('/objects/(:alphanum)', 'Admin::objectsList/$1', ['subdomain' => 'nothere']);
        $routes->add('/objects/(:alphanum)', 'App::objectsList/$1', ['subdomain' => '*']);

        $expects = [
            'objects/([a-zA-Z0-9]+)' => '\App::objectsList/$1',
        ];

        $this->assertSame($expects, $routes->getRoutes());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5959
     */
    public function testWithNoSubdomainAndDot()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $routes = $this->getCollector();

        $routes->add('/objects/(:alphanum)', 'App::objectsList/$1', ['subdomain' => '*']);

        $this->assertSame([], $routes->getRoutes());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1692
     */
    public function testWithSubdomainOrdered()
    {
        $_SERVER['HTTP_HOST'] = 'adm.example.com';

        $routes = $this->getCollector();

        $routes->add('/objects/(:alphanum)', 'App::objectsList/$1');
        $routes->add('/objects/(:alphanum)', 'Admin::objectsList/$1', ['subdomain' => 'adm']);

        $expects = [
            'objects/([a-zA-Z0-9]+)' => '\Admin::objectsList/$1',
        ];

        $this->assertSame($expects, $routes->getRoutes());
    }

    public function testWillDiscoverLocal()
    {
        $config = ['SampleSpace' => TESTPATH . '_support'];

        $moduleConfig          = new Modules();
        $moduleConfig->enabled = true;

        $routes = $this->getCollector($config, [], $moduleConfig);

        $match = $routes->getRoutes();

        $this->assertArrayHasKey('testing', $match);
        $this->assertSame($match['testing'], '\TestController::index');
    }

    public function testDiscoverLocalAllowsConfigToOverridePackages()
    {
        $config = [
            'SampleSpace' => TESTPATH . '_support',
        ];

        $moduleConfig          = new Modules();
        $moduleConfig->enabled = true;

        $routes = $this->getCollector($config, [], $moduleConfig);

        $routes->add('testing', 'MainRoutes::index', ['as' => 'testing-index']);

        $match = $routes->getRoutes();

        $this->assertArrayHasKey('testing', $match);
        $this->assertSame($match['testing'], '\MainRoutes::index');
    }

    public function testRoutesOptions()
    {
        $routes = $this->getCollector();

        // options need to be declared separately, to not confuse PHPCBF
        $options = [
            'as'  => 'admin',
            'foo' => 'baz',
        ];
        $routes->add(
            'administrator',
            static function () {},
            $options
        );

        $options = $routes->getRoutesOptions('administrator');

        $this->assertSame($options, ['as' => 'admin', 'foo' => 'baz']);
    }

    public function testRoutesOptionsForDifferentVerbs()
    {
        $routes = $this->getCollector();

        // options need to be declared separately, to not confuse PHPCBF
        $options1 = [
            'as'  => 'admin1',
            'foo' => 'baz1',
        ];
        $options2 = [
            'as'  => 'admin2',
            'foo' => 'baz2',
        ];
        $options3 = [
            'bar' => 'baz',
        ];
        $routes->get(
            'administrator',
            static function () {},
            $options1
        );
        $routes->post(
            'administrator',
            static function () {},
            $options2
        );
        $routes->add(
            'administrator',
            static function () {},
            $options3
        );

        $options = $routes->getRoutesOptions('administrator');

        $this->assertSame($options, ['as' => 'admin1', 'foo' => 'baz1', 'bar' => 'baz']);

        $options = $routes->setHTTPVerb('post')->getRoutesOptions('administrator');

        $this->assertSame($options, ['as' => 'admin2', 'foo' => 'baz2', 'bar' => 'baz']);

        $options = $routes->setHTTPVerb('get')->getRoutesOptions('administrator', 'post');

        $this->assertSame($options, ['as' => 'admin2', 'foo' => 'baz2', 'bar' => 'baz']);
    }

    public function testRouteGroupWithFilterSimple()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $routes->group(
            'admin',
            ['filter' => 'role'],
            static function ($routes) {
                $routes->add('users', '\Users::list');
            }
        );

        $this->assertTrue($routes->isFiltered('admin/users'));
        $this->assertFalse($routes->isFiltered('admin/franky'));
        $this->assertSame(['role'], $routes->getFiltersForRoute('admin/users'));
        $this->assertSame([], $routes->getFiltersForRoute('admin/bosses'));
    }

    public function testRouteGroupWithFilterWithParams()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $routes->group(
            'admin',
            ['filter' => 'role:admin,manager'],
            static function ($routes) {
                $routes->add('users', '\Users::list');
            }
        );

        $this->assertTrue($routes->isFiltered('admin/users'));
        $this->assertFalse($routes->isFiltered('admin/franky'));
        $this->assertSame(['role:admin,manager'], $routes->getFiltersForRoute('admin/users'));
    }

    public function test404OverrideNot()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $this->assertNull($routes->get404Override());
    }

    public function test404OverrideString()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $routes->set404Override('Explode');
        $this->assertSame('Explode', $routes->get404Override());
    }

    public function test404OverrideCallable()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $routes->set404Override(static function () {
            echo 'Explode now';
        });
        $this->assertIsCallable($routes->get404Override());
    }

    public function testOffsetParameters()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();

        $routes->get('users/(:num)', 'users/show/$1', ['offset' => 1]);
        $expected = ['users/([0-9]+)' => '\users/show/$2'];
        $this->assertSame($expected, $routes->getRoutes());
    }

    /**
     * Battery of tests for reported issue
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1697
     */
    public function testRouteToWithSubdomainMatch()
    {
        $_SERVER['HTTP_HOST'] = 'doc.example.com';
        Services::request()->setMethod('get');

        $routes = $this->getCollector();

        $routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['subdomain' => 'doc', 'as' => 'doc_item']);

        $this->assertSame('/i/sth', $routes->reverseRoute('doc_item', 'sth'));
    }

    public function testRouteToWithSubdomainMismatch()
    {
        $_SERVER['HTTP_HOST'] = 'dev.example.com';
        Services::request()->setMethod('get');

        $routes = $this->getCollector();

        $routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['subdomain' => 'doc', 'as' => 'doc_item']);

        $this->assertFalse($routes->reverseRoute('doc_item', 'sth'));
    }

    public function testRouteToWithSubdomainNot()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        Services::request()->setMethod('get');

        $routes = $this->getCollector();

        $routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['subdomain' => 'doc', 'as' => 'doc_item']);

        $this->assertFalse($routes->reverseRoute('doc_item', 'sth'));
    }

    public function testRouteToWithGenericSubdomainMatch()
    {
        $_SERVER['HTTP_HOST'] = 'doc.example.com';
        Services::request()->setMethod('get');

        $routes = $this->getCollector();

        $routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['subdomain' => '*', 'as' => 'doc_item']);

        $this->assertSame('/i/sth', $routes->reverseRoute('doc_item', 'sth'));
    }

    public function testRouteToWithGenericSubdomainMismatch()
    {
        $_SERVER['HTTP_HOST'] = 'dev.example.com';
        Services::request()->setMethod('get');

        $routes = $this->getCollector();

        $routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['subdomain' => '*', 'as' => 'doc_item']);

        $this->assertSame('/i/sth', $routes->reverseRoute('doc_item', 'sth'));
    }

    public function testRouteToWithGenericSubdomainNot()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        Services::request()->setMethod('get');

        $routes = $this->getCollector();

        $routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['subdomain' => '*', 'as' => 'doc_item']);

        $this->assertFalse($routes->reverseRoute('doc_item', 'sth'));
    }

    public function testRouteToWithoutSubdomainMatch()
    {
        $_SERVER['HTTP_HOST'] = 'doc.example.com';
        Services::request()->setMethod('get');

        $routes = $this->getCollector();

        $routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['hostname' => 'example.com', 'as' => 'doc_item']);

        $this->assertFalse($routes->reverseRoute('doc_item', 'sth'));
    }

    public function testRouteToWithoutSubdomainMismatch()
    {
        $_SERVER['HTTP_HOST'] = 'dev.example.com';
        Services::request()->setMethod('get');

        $routes = $this->getCollector();

        $routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['hostname' => 'example.com', 'as' => 'doc_item']);

        $this->assertFalse($routes->reverseRoute('doc_item', 'sth'));
    }

    public function testRouteToWithoutSubdomainNot()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        Services::request()->setMethod('get');

        $routes = $this->getCollector();

        $routes->get('i/(:any)', 'App\Controllers\Site\CDoc::item/$1', ['hostname' => 'example.com', 'as' => 'doc_item']);

        $this->assertSame('/i/sth', $routes->reverseRoute('doc_item', 'sth'));
    }

    /**
     * Tests for router overwritting issue
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1692
     */
    public function testRouteOverwritingDifferentSubdomains()
    {
        $_SERVER['HTTP_HOST'] = 'doc.domain.com';
        Services::request()->setMethod('get');

        $routes = $this->getCollector();
        $router = new Router($routes, Services::request());

        $routes->setDefaultNamespace('App\Controllers');
        $routes->setDefaultController('Home');
        $routes->setDefaultMethod('index');
        $routes->setHTTPVerb('get');

        $routes->get('/', '\App\Controllers\Site\CDoc::index', ['subdomain' => 'doc', 'as' => 'doc_index']);
        $routes->get('/', 'Home::index', ['subdomain' => 'dev']);

        $expects = '\App\Controllers\Site\CDoc';

        $this->assertSame($expects, $router->handle('/'));
    }

    public function testRouteOverwritingTwoRules()
    {
        $_SERVER['HTTP_HOST'] = 'doc.domain.com';
        Services::request()->setMethod('get');

        $routes = $this->getCollector();
        $router = new Router($routes, Services::request());

        $routes->setDefaultNamespace('App\Controllers');
        $routes->setDefaultController('Home');
        $routes->setDefaultMethod('index');

        $routes->get('/', '\App\Controllers\Site\CDoc::index', ['subdomain' => 'doc', 'as' => 'doc_index']);
        $routes->get('/', 'Home::index');

        // the second rule applies, so overwrites the first
        $expects = '\\' . Home::class;

        $this->assertSame($expects, $router->handle('/'));
    }

    public function testRouteOverwritingTwoRulesLastApplies()
    {
        $_SERVER['HTTP_HOST'] = 'doc.domain.com';
        Services::request()->setMethod('get');

        $routes = $this->getCollector();
        $router = new Router($routes, Services::request());

        $routes->setDefaultNamespace('App\Controllers');
        $routes->setDefaultController('Home');
        $routes->setDefaultMethod('index');

        $routes->get('/', 'Home::index');
        $routes->get('/', '\App\Controllers\Site\CDoc::index', ['subdomain' => 'doc', 'as' => 'doc_index']);

        $expects = '\App\Controllers\Site\CDoc';

        $this->assertSame($expects, $router->handle('/'));
    }

    public function testRouteOverwritingMatchingSubdomain()
    {
        $_SERVER['HTTP_HOST'] = 'doc.domain.com';
        Services::request()->setMethod('get');

        $routes = $this->getCollector();
        $router = new Router($routes, Services::request());

        $routes->setDefaultNamespace('App\Controllers');
        $routes->setDefaultController('Home');
        $routes->setDefaultMethod('index');

        $routes->get('/', 'Home::index', ['as' => 'ddd']);
        $routes->get('/', '\App\Controllers\Site\CDoc::index', ['subdomain' => 'doc', 'as' => 'doc_index']);

        $expects = '\App\Controllers\Site\CDoc';

        $this->assertSame($expects, $router->handle('/'));
    }

    public function testRouteOverwritingMatchingHost()
    {
        $_SERVER['HTTP_HOST'] = 'doc.domain.com';
        Services::request()->setMethod('get');

        $routes = $this->getCollector();
        $router = new Router($routes, Services::request());

        $routes->setDefaultNamespace('App\Controllers');
        $routes->setDefaultController('Home');
        $routes->setDefaultMethod('index');

        $routes->get('/', 'Home::index', ['as' => 'ddd']);
        $routes->get('/', '\App\Controllers\Site\CDoc::index', ['hostname' => 'doc.domain.com', 'as' => 'doc_index']);

        $expects = '\App\Controllers\Site\CDoc';

        $this->assertSame($expects, $router->handle('/'));
    }

    /**
     * Tests for router DefaultNameSpace issue
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2423
     */
    public function testRouteDefaultNameSpace()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();
        $router = new Router($routes, Services::request());

        $routes->setDefaultNamespace('App\Controllers');
        $routes->get('/', 'Core\Home::index');

        $expects = '\App\Controllers\Core\Home';

        $this->assertSame($expects, $router->handle('/'));
    }

    public function testZeroAsURIPath()
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();
        $router = new Router($routes, Services::request());

        $routes->setDefaultNamespace('App\Controllers');
        $routes->get('/0', 'Core\Home::index');

        $expects = '\App\Controllers\Core\Home';

        $this->assertSame($expects, $router->handle('/0'));
    }

    public function provideRouteDefaultNamespace()
    {
        return [
            'with \\ prefix'    => ['\App\Controllers'],
            'without \\ prefix' => ['App\Controllers'],
        ];
    }

    /**
     * @dataProvider provideRouteDefaultNamespace
     *
     * @param mixed $namespace
     */
    public function testAutoRoutesControllerNameReturnsFQCN($namespace)
    {
        $routes = $this->getCollector();
        $routes->setAutoRoute(true);
        $routes->setDefaultNamespace($namespace);

        $router = new Router($routes, Services::request());
        $router->handle('/product');

        $this->assertSame('\App\\Controllers\\Product', $router->controllerName());
    }

    /**
     * @dataProvider provideRouteDefaultNamespace
     *
     * @param mixed $namespace
     */
    public function testRoutesControllerNameReturnsFQCN($namespace)
    {
        Services::request()->setMethod('get');
        $routes = $this->getCollector();
        $routes->setAutoRoute(false);
        $routes->setDefaultNamespace($namespace);
        $routes->get('/product', 'Product');

        $router = new Router($routes, Services::request());
        $router->handle('/product');

        $this->assertSame('\App\\Controllers\\Product', $router->controllerName());
    }

    public function testRoutePriorityDetected()
    {
        $collection = $this->getCollector();

        $this->assertFalse($this->getPrivateProperty($collection, 'prioritizeDetected'));

        $collection->add('/', 'Controller::method', ['priority' => 0]);

        $this->assertFalse($this->getPrivateProperty($collection, 'prioritizeDetected'));

        $collection->add('priority', 'Controller::method', ['priority' => 1]);

        $this->assertTrue($this->getPrivateProperty($collection, 'prioritizeDetected'));
    }

    public function testRoutePriorityValue()
    {
        $collection = $this->getCollector();

        $collection->add('string', 'Controller::method', ['priority' => 'string']);
        $this->assertSame(0, $collection->getRoutesOptions('string')['priority']);

        $collection->add('negative-integer', 'Controller::method', ['priority' => -1]);
        $this->assertSame(1, $collection->getRoutesOptions('negative-integer')['priority']);

        $collection->add('string-negative-integer', 'Controller::method', ['priority' => '-1']);
        $this->assertSame(1, $collection->getRoutesOptions('string-negative-integer')['priority']);
    }

    public function testGetRegisteredControllersReturnsControllerForHTTPverb()
    {
        $collection = $this->getCollector();
        $collection->get('test', '\App\Controllers\Hello::get');
        $collection->post('test', '\App\Controllers\Hello::post');

        $routes = $collection->getRegisteredControllers('get');

        $expects = [
            '\App\Controllers\Hello',
        ];
        $this->assertSame($expects, $routes);

        $routes = $collection->getRegisteredControllers('post');

        $expects = [
            '\App\Controllers\Hello',
        ];
        $this->assertSame($expects, $routes);
    }

    public function testGetRegisteredControllersReturnsTwoControllers()
    {
        $collection = $this->getCollector();
        $collection->post('test', '\App\Controllers\Test::post');
        $collection->post('hello', '\App\Controllers\Hello::post');

        $routes = $collection->getRegisteredControllers('post');

        $expects = [
            '\App\Controllers\Test',
            '\App\Controllers\Hello',
        ];
        $this->assertSame($expects, $routes);
    }

    public function testGetRegisteredControllersReturnsOneControllerWhenTwoRoutsWithDiffernetMethods()
    {
        $collection = $this->getCollector();
        $collection->post('test', '\App\Controllers\Test::test');
        $collection->post('hello', '\App\Controllers\Test::hello');

        $routes = $collection->getRegisteredControllers('post');

        $expects = [
            '\App\Controllers\Test',
        ];
        $this->assertSame($expects, $routes);
    }

    public function testGetRegisteredControllersReturnsAllControllers()
    {
        $collection = $this->getCollector();
        $collection->get('test', '\App\Controllers\Hello::get');
        $collection->post('test', '\App\Controllers\Hello::post');
        $collection->post('hello', '\App\Controllers\Test::hello');

        $routes = $collection->getRegisteredControllers('*');

        $expects = [
            '\App\Controllers\Hello',
            '\App\Controllers\Test',
        ];
        $this->assertSame($expects, $routes);
    }

    public function testGetRegisteredControllersReturnsControllerByAddMethod()
    {
        $collection = $this->getCollector();
        $collection->get('test', '\App\Controllers\Hello::get');
        $collection->add('hello', '\App\Controllers\Test::hello');

        $routes = $collection->getRegisteredControllers('get');

        $expects = [
            '\App\Controllers\Hello',
            '\App\Controllers\Test',
        ];
        $this->assertSame($expects, $routes);
    }

    public function testGetRegisteredControllersDoesNotReturnClosures()
    {
        $collection = $this->getCollector();
        $collection->get('feed', static function () {
        });

        $routes = $collection->getRegisteredControllers('*');

        $expects = [];
        $this->assertSame($expects, $routes);
    }
}
