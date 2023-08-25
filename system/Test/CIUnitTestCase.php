<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Config\Factories;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\Database\Seeder;
use CodeIgniter\Events\Events;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Test\Mock\MockCache;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use CodeIgniter\Test\Mock\MockEmail;
use CodeIgniter\Test\Mock\MockSession;
use Config\App;
use Config\Autoload;
use Config\Email;
use Config\Modules;
use Config\Services;
use Config\Session;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Framework test case for PHPUnit.
 */
abstract class CIUnitTestCase extends TestCase
{
    use ReflectionHelper;

    /**
     * @var CodeIgniter
     */
    protected $app;

    /**
     * Methods to run during setUp.
     *
     * WARNING: Do not override unless you know exactly what you are doing.
     *          This property may be deprecated in the future.
     *
     * @var array of methods
     */
    protected $setUpMethods = [
        'resetFactories',
        'mockCache',
        'mockEmail',
        'mockSession',
    ];

    /**
     * Methods to run during tearDown.
     *
     * WARNING: This property may be deprecated in the future.
     *
     * @var array of methods
     */
    protected $tearDownMethods = [];

    /**
     * Store of identified traits.
     */
    private ?array $traits = null;

    // --------------------------------------------------------------------
    // Database Properties
    // --------------------------------------------------------------------

    /**
     * Should run db migration?
     *
     * @var bool
     */
    protected $migrate = true;

    /**
     * Should run db migration only once?
     *
     * @var bool
     */
    protected $migrateOnce = false;

    /**
     * Should run seeding only once?
     *
     * @var bool
     */
    protected $seedOnce = false;

    /**
     * Should the db be refreshed before test?
     *
     * @var bool
     */
    protected $refresh = true;

    /**
     * The seed file(s) used for all tests within this test case.
     * Should be fully-namespaced or relative to $basePath
     *
     * @var array|string
     */
    protected $seed = '';

    /**
     * The path to the seeds directory.
     * Allows overriding the default application directories.
     *
     * @var string
     */
    protected $basePath = SUPPORTPATH . 'Database';

    /**
     * The namespace(s) to help us find the migration classes.
     * Empty is equivalent to running `spark migrate --all`.
     * Note that running "all" runs migrations in date order,
     * but specifying namespaces runs them in namespace order (then date)
     *
     * @var array|string|null
     */
    protected $namespace = 'Tests\Support';

    /**
     * The name of the database group to connect to.
     * If not present, will use the defaultGroup.
     *
     * @var string
     * @phpstan-var non-empty-string
     */
    protected $DBGroup = 'tests';

    /**
     * Our database connection.
     *
     * @var BaseConnection
     */
    protected $db;

    /**
     * Migration Runner instance.
     *
     * @var MigrationRunner|null
     */
    protected $migrations;

    /**
     * Seeder instance
     *
     * @var Seeder
     */
    protected $seeder;

    /**
     * Stores information needed to remove any
     * rows inserted via $this->hasInDatabase();
     *
     * @var array
     */
    protected $insertCache = [];

    // --------------------------------------------------------------------
    // Feature Properties
    // --------------------------------------------------------------------

    /**
     * If present, will override application
     * routes when using call().
     *
     * @var RouteCollection|null
     */
    protected $routes;

    /**
     * Values to be set in the SESSION global
     * before running the test.
     *
     * @var array
     */
    protected $session = [];

    /**
     * Enabled auto clean op buffer after request call
     *
     * @var bool
     */
    protected $clean = true;

    /**
     * Custom request's headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Allows for formatting the request body to what
     * the controller is going to expect
     *
     * @var string
     */
    protected $bodyFormat = '';

    /**
     * Allows for directly setting the body to what
     * it needs to be.
     *
     * @var mixed
     */
    protected $requestBody = '';

    // --------------------------------------------------------------------
    // Staging
    // --------------------------------------------------------------------

    /**
     * Load the helpers.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        helper(['url', 'test']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->app) {
            $this->app = $this->createApplication();
        }

        foreach ($this->setUpMethods as $method) {
            $this->{$method}();
        }

        // Check for the database trait
        if (method_exists($this, 'setUpDatabase')) {
            $this->setUpDatabase();
        }

        // Check for other trait methods
        $this->callTraitMethods('setUp');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->tearDownMethods as $method) {
            $this->{$method}();
        }

        // Check for the database trait
        if (method_exists($this, 'tearDownDatabase')) {
            $this->tearDownDatabase();
        }

        // Check for other trait methods
        $this->callTraitMethods('tearDown');
    }

    /**
     * Checks for traits with corresponding
     * methods for setUp or tearDown.
     *
     * @param string $stage 'setUp' or 'tearDown'
     */
    private function callTraitMethods(string $stage): void
    {
        if ($this->traits === null) {
            $this->traits = class_uses_recursive($this);
        }

        foreach ($this->traits as $trait) {
            $method = $stage . class_basename($trait);

            if (method_exists($this, $method)) {
                $this->{$method}();
            }
        }
    }

    // --------------------------------------------------------------------
    // Mocking
    // --------------------------------------------------------------------

    /**
     * Resets shared instanced for all Factories components
     */
    protected function resetFactories()
    {
        Factories::reset();
    }

    /**
     * Resets shared instanced for all Services
     */
    protected function resetServices(bool $initAutoloader = true)
    {
        Services::reset($initAutoloader);
    }

    /**
     * Injects the mock Cache driver to prevent filesystem collisions
     */
    protected function mockCache()
    {
        Services::injectMock('cache', new MockCache());
    }

    /**
     * Injects the mock email driver so no emails really send
     */
    protected function mockEmail()
    {
        Services::injectMock('email', new MockEmail(config(Email::class)));
    }

    /**
     * Injects the mock session driver into Services
     */
    protected function mockSession()
    {
        $_SESSION = [];

        $config  = config(Session::class);
        $session = new MockSession(new ArrayHandler($config, '0.0.0.0'), $config);

        Services::injectMock('session', $session);
    }

    // --------------------------------------------------------------------
    // Assertions
    // --------------------------------------------------------------------

    /**
     * Custom function to hook into CodeIgniter's Logging mechanism
     * to check if certain messages were logged during code execution.
     *
     * @param string|null $expectedMessage
     *
     * @return bool
     */
    public function assertLogged(string $level, $expectedMessage = null)
    {
        $result = TestLogger::didLog($level, $expectedMessage);

        $this->assertTrue($result, sprintf(
            'Failed asserting that expected message "%s" with level "%s" was logged.',
            $expectedMessage ?? '',
            $level
        ));

        return $result;
    }

    /**
     * Asserts that there is a log record that contains `$logMessage` in the message.
     */
    public function assertLogContains(string $level, string $logMessage, string $message = ''): void
    {
        $this->assertTrue(
            TestLogger::didLog($level, $logMessage, false),
            $message ?: sprintf(
                'Failed asserting that logs have a record of message containing "%s" with level "%s".',
                $logMessage,
                $level
            )
        );
    }

    /**
     * Hooks into CodeIgniter's Events system to check if a specific
     * event was triggered or not.
     *
     * @throws Exception
     */
    public function assertEventTriggered(string $eventName): bool
    {
        $found     = false;
        $eventName = strtolower($eventName);

        foreach (Events::getPerformanceLogs() as $log) {
            if ($log['event'] !== $eventName) {
                continue;
            }

            $found = true;
            break;
        }

        $this->assertTrue($found);

        return $found;
    }

    /**
     * Hooks into xdebug's headers capture, looking for presence of
     * a specific header emitted.
     *
     * @param string $header The leading portion of the header we are looking for
     */
    public function assertHeaderEmitted(string $header, bool $ignoreCase = false): void
    {
        $this->assertNotNull(
            $this->getHeaderEmitted($header, $ignoreCase, __METHOD__),
            "Didn't find header for {$header}"
        );
    }

    /**
     * Hooks into xdebug's headers capture, looking for absence of
     * a specific header emitted.
     *
     * @param string $header The leading portion of the header we don't want to find
     */
    public function assertHeaderNotEmitted(string $header, bool $ignoreCase = false): void
    {
        $this->assertNull(
            $this->getHeaderEmitted($header, $ignoreCase, __METHOD__),
            "Found header for {$header}"
        );
    }

    /**
     * Custom function to test that two values are "close enough".
     * This is intended for extended execution time testing,
     * where the result is close but not exactly equal to the
     * expected time, for reasons beyond our control.
     *
     * @param mixed $actual
     *
     * @throws Exception
     */
    public function assertCloseEnough(int $expected, $actual, string $message = '', int $tolerance = 1)
    {
        $difference = abs($expected - (int) floor($actual));

        $this->assertLessThanOrEqual($tolerance, $difference, $message);
    }

    /**
     * Custom function to test that two values are "close enough".
     * This is intended for extended execution time testing,
     * where the result is close but not exactly equal to the
     * expected time, for reasons beyond our control.
     *
     * @param mixed $expected
     * @param mixed $actual
     *
     * @return bool|void
     *
     * @throws Exception
     */
    public function assertCloseEnoughString($expected, $actual, string $message = '', int $tolerance = 1)
    {
        $expected = (string) $expected;
        $actual   = (string) $actual;
        if (strlen($expected) !== strlen($actual)) {
            return false;
        }

        try {
            $expected   = (int) substr($expected, -2);
            $actual     = (int) substr($actual, -2);
            $difference = abs($expected - $actual);

            $this->assertLessThanOrEqual($tolerance, $difference, $message);
        } catch (Exception $e) {
            return false;
        }
    }

    // --------------------------------------------------------------------
    // Utility
    // --------------------------------------------------------------------

    /**
     * Loads up an instance of CodeIgniter
     * and gets the environment setup.
     *
     * @return CodeIgniter
     */
    protected function createApplication()
    {
        // Initialize the autoloader.
        Services::autoloader()->initialize(new Autoload(), new Modules());

        $app = new MockCodeIgniter(new App());
        $app->initialize();

        return $app;
    }

    /**
     * Return first matching emitted header.
     */
    protected function getHeaderEmitted(string $header, bool $ignoreCase = false, string $method = __METHOD__): ?string
    {
        if (! function_exists('xdebug_get_headers')) {
            $this->markTestSkipped($method . '() requires xdebug.');
        }

        foreach (xdebug_get_headers() as $emittedHeader) {
            $found = $ignoreCase
                ? (stripos($emittedHeader, $header) === 0)
                : (strpos($emittedHeader, $header) === 0);

            if ($found) {
                return $emittedHeader;
            }
        }

        return null;
    }
}
