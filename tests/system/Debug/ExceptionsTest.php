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
use CodeIgniter\Honeypot\Exceptions\HoneypotException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use Config\Exceptions as ExceptionsConfig;
use ErrorException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ExceptionsTest extends CIUnitTestCase
{
    use ReflectionHelper;

    private Exceptions $exception;

    #[Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        service('superglobals')->unsetServer('CODEIGNITER_SCREAM_DEPRECATIONS');
    }

    #[Override]
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        service('superglobals')->setServer('CODEIGNITER_SCREAM_DEPRECATIONS', '1');
    }

    #[Override]
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

    #[DataProvider('provideVendorSplObjectHashDeprecationDoesNotThrow')]
    public function testVendorSplObjectHashDeprecationDoesNotThrow(string $file): void
    {
        $config = new ExceptionsConfig();

        $config->logDeprecations     = false;
        $config->deprecationLogLevel = 'error';

        $this->exception = new Exceptions($config);

        $message = 'Function spl_object_hash() is deprecated since 8.6, consider using spl_object_id() instead';

        $this->assertTrue($this->exception->errorHandler(E_DEPRECATED, $message, $file, 374));
        $this->assertLogContains('error', '[DEPRECATED] ' . $message);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideVendorSplObjectHashDeprecationDoesNotThrow(): iterable
    {
        yield 'vfsStream' => [VENDORPATH . 'mikey179/vfsstream/src/main/php/org/bovigo/vfs/vfsStreamFile.php'];

        yield 'Kint' => [VENDORPATH . 'kint-php/kint/src/Parser/Parser.php'];
    }

    public function testSplObjectHashDeprecationOutsideVendorStillThrows(): void
    {
        $config = new ExceptionsConfig();

        $config->logDeprecations = false;

        $this->exception = new Exceptions($config);

        $message = 'Function spl_object_hash() is deprecated since 8.6, consider using spl_object_id() instead';

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage($message);

        $this->exception->errorHandler(
            E_DEPRECATED,
            $message,
            APPPATH . 'Foo.php',
            1,
        );
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
        $this->assertSame([403, EXIT_CONFIG], $determineCodes(HoneypotException::isBot()));
    }

    public function testResetForWorkerModeClearsPreviousRequestState(): void
    {
        $this->setPrivateProperty($this->exception, 'exceptionCaughtByExceptionHandler', new RuntimeException('Previous request'));
        $this->setPrivateProperty($this->exception, 'request', service('request'));
        $this->setPrivateProperty($this->exception, 'response', service('response'));

        $this->exception->resetForWorkerMode();

        $this->assertNull($this->getPrivateProperty($this->exception, 'exceptionCaughtByExceptionHandler'));
        $this->assertNull($this->getPrivateProperty($this->exception, 'request'));
        $this->assertNull($this->getPrivateProperty($this->exception, 'response'));
    }
}
