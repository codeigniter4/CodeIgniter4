<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Entity\Exceptions\CastException;
use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use Config\Exceptions as ExceptionsConfig;
use ErrorException;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ExceptionsTest extends CIUnitTestCase
{
    use ReflectionHelper;

    private Exceptions $exception;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        unset($_SERVER['CODEIGNITER_SCREAM_DEPRECATIONS']);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $_SERVER['CODEIGNITER_SCREAM_DEPRECATIONS'] = '1';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->exception = new Exceptions(new ExceptionsConfig());
    }

    public function testDeprecationsOnPhp81DoNotThrow(): void
    {
        $config = new ExceptionsConfig();

        $config->logDeprecations     = true;
        $config->deprecationLogLevel = 'error';

        $this->exception = new Exceptions($config);
        $this->exception->initialize();

        try {
            $result = str_contains('foobar', null); // @phpstan-ignore argument.type (Needed for testing)
            $this->assertLogContains('error', '[DEPRECATED] str_contains(): ');
        } catch (ErrorException) {
            $this->fail('The catch block should not be reached.');
        } finally {
            restore_error_handler();
            restore_exception_handler();
        }
    }

    public function testSuppressedDeprecationsAreLogged(): void
    {
        $config = new ExceptionsConfig();

        $config->logDeprecations     = true;
        $config->deprecationLogLevel = 'error';

        $this->exception = new Exceptions($config);
        $this->exception->initialize();

        @trigger_error('Hello! I am a deprecation!', E_USER_DEPRECATED);
        $this->assertLogContains('error', '[DEPRECATED] Hello! I am a deprecation!');

        restore_error_handler();
        restore_exception_handler();
    }

    public function testDetermineCodes(): void
    {
        $determineCodes = self::getPrivateMethodInvoker($this->exception, 'determineCodes');

        $this->assertSame([500, EXIT_ERROR], $determineCodes(new RuntimeException('This.')));
        $this->assertSame([500, EXIT_ERROR], $determineCodes(new RuntimeException('That.', 600)));
        $this->assertSame([500, EXIT_ERROR], $determineCodes(new RuntimeException('There.', 404)));
        $this->assertSame([500, EXIT_ERROR], $determineCodes(new RuntimeException('This.', 167)));
        $this->assertSame([500, EXIT_CONFIG], $determineCodes(new ConfigException('This.')));
        $this->assertSame([500, EXIT_CONFIG], $determineCodes(CastException::forInvalidInterface('This.')));
        $this->assertSame([500, EXIT_DATABASE], $determineCodes(new DatabaseException('This.')));
    }
}
