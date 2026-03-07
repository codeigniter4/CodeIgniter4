<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Log;

use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Log\Exceptions\LogException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockLogger as LoggerConfig;
use PHPUnit\Framework\Attributes\Group;
use ReflectionMethod;
use ReflectionNamedType;
use Tests\Support\Log\Handlers\TestHandler;

/**
 * @internal
 */
#[Group('Others')]
final class LoggerTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        // Reset the current time.
        Time::setTestNow();

        service('context')->clearAll(); // Clear any context data that may have been set during tests.
    }

    public function testThrowsExceptionWithBadHandlerSettings(): void
    {
        $config           = new LoggerConfig();
        $config->handlers = [];

        $this->expectException(FrameworkException::class);
        $this->expectExceptionMessage(lang('Core.noHandlers', ['LoggerConfig']));

        new Logger($config);
    }

    public function testLogThrowsExceptionOnInvalidLevel(): void
    {
        $config = new LoggerConfig();

        $this->expectException(LogException::class);
        $this->expectExceptionMessage(lang('Log.invalidLogLevel', ['foo']));

        $logger = new Logger($config);

        $logger->log('foo', '');
    }

    public function testLogAlwaysReturnsVoid(): void
    {
        $config            = new LoggerConfig();
        $config->threshold = 3;

        $logger = new Logger($config);

        $refMethod = new ReflectionMethod($logger, 'log');
        $this->assertTrue($refMethod->hasReturnType());
        $this->assertInstanceOf(ReflectionNamedType::class, $refMethod->getReturnType());
        $this->assertSame('void', $refMethod->getReturnType()->getName());
    }

    public function testLogActuallyLogs(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $expected = 'DEBUG - ' . Time::now()->format('Y-m-d') . ' --> Test message';
        $logger->log('debug', 'Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testLogDoesnotLogUnhandledLevels(): void
    {
        $config = new LoggerConfig();

        $config->handlers[TestHandler::class]['handles'] = ['critical'];

        $logger = new Logger($config);

        $logger->log('debug', 'Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(0, $logs);
    }

    public function testLogInterpolatesMessage(): void
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $expected = 'DEBUG - ' . Time::now()->format('Y-m-d') . ' --> Test message bar baz';

        $logger->log('debug', 'Test message {foo} {bar}', ['foo' => 'bar', 'bar' => 'baz']);

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testLogInterpolatesPost(): void
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        service('superglobals')->setPost('foo', 'bar');
        $expected = 'DEBUG - ' . Time::now()->format('Y-m-d') . ' --> Test message $_POST: ' . print_r(service('superglobals')->getPostArray(), true);

        $logger->log('debug', 'Test message {post_vars}');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testLogInterpolatesGet(): void
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        service('superglobals')->setGet('bar', 'baz');
        $expected = 'DEBUG - ' . Time::now()->format('Y-m-d') . ' --> Test message $_GET: ' . print_r(service('superglobals')->getGetArray(), true);

        $logger->log('debug', 'Test message {get_vars}');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testLogInterpolatesSession(): void
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $_SESSION = ['xxx' => 'yyy'];
        $expected = 'DEBUG - ' . Time::now()->format('Y-m-d') . ' --> Test message $_SESSION: ' . print_r($_SESSION, true);

        $logger->log('debug', 'Test message {session_vars}');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testLogInterpolatesCurrentEnvironment(): void
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $expected = 'DEBUG - ' . Time::now()->format('Y-m-d') . ' --> Test message ' . ENVIRONMENT;

        $logger->log('debug', 'Test message {env}');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testLogInterpolatesEnvironmentVars(): void
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $_ENV['foo'] = 'bar';

        $expected = 'DEBUG - ' . Time::now()->format('Y-m-d') . ' --> Test message bar';

        $logger->log('debug', 'Test message {env:foo}');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testLogInterpolatesFileAndLine(): void
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        $_ENV['foo'] = 'bar';

        $logger->log('debug', 'Test message {file} {line}');
        $line     = __LINE__ - 1;
        $expected = "LoggerTest.php {$line}";

        $logs = TestHandler::getLogs();

        $this->assertGreaterThan(1, strpos($logs[0], $expected));
    }

    public function testLogInterpolatesLineOnly(): void
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        $_ENV['foo'] = 'bar';

        $logger->log('debug', 'Test message Sample {line}');
        $line     = __LINE__ - 1;
        $expected = "Sample {$line}";

        $logs = TestHandler::getLogs();

        $this->assertGreaterThan(1, strpos($logs[0], $expected));
    }

    public function testLogInterpolatesExceptions(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $expected = 'ERROR - ' . Time::now()->format('Y-m-d') . ' --> [ERROR] These are not the droids you are looking for';

        try {
            throw new RuntimeException('These are not the droids you are looking for');
        } catch (RuntimeException $e) {
            $logger->log('error', '[ERROR] {exception}', ['exception' => $e]);
        }

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame(0, strpos($logs[0], $expected));
    }

    public function testEmergencyLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $expected = 'EMERGENCY - ' . Time::now()->format('Y-m-d') . ' --> Test message';

        $logger->emergency('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testAlertLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $expected = 'ALERT - ' . Time::now()->format('Y-m-d') . ' --> Test message';

        $logger->alert('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testCriticalLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $expected = 'CRITICAL - ' . Time::now()->format('Y-m-d') . ' --> Test message';

        $logger->critical('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testErrorLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $expected = 'ERROR - ' . Time::now()->format('Y-m-d') . ' --> Test message';

        $logger->error('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testWarningLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $expected = 'WARNING - ' . Time::now()->format('Y-m-d') . ' --> Test message';

        $logger->warning('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testNoticeLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $expected = 'NOTICE - ' . Time::now()->format('Y-m-d') . ' --> Test message';

        $logger->notice('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testInfoLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $expected = 'INFO - ' . Time::now()->format('Y-m-d') . ' --> Test message';

        $logger->info('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testDebugLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $expected = 'DEBUG - ' . Time::now()->format('Y-m-d') . ' --> Test message';

        $logger->debug('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testLogLevels(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        Time::setTestNow('2023-11-25 12:00:00');

        $expected = 'WARNING - ' . Time::now()->format('Y-m-d') . ' --> Test message';

        $logger->log(5, 'Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testDetermineFileNoStackTrace(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = [
            'unknown',
            'unknown',
        ];

        $this->assertSame($expected, $logger->determineFile());
    }

    public function testLogsGlobalContext(): void
    {
        $config                   = new LoggerConfig();
        $config->logGlobalContext = true;

        $logger = new Logger($config);

        Time::setTestNow('2026-02-18 12:00:00');

        service('context')->set('foo', 'bar');

        $expectedMessage = 'DEBUG - ' . Time::now()->format('Y-m-d') . ' --> Test message';

        $logger->log('debug', 'Test message');

        $logs     = TestHandler::getLogs();
        $contexts = TestHandler::getContexts();

        $this->assertCount(1, $logs);
        $this->assertSame($expectedMessage, $logs[0]);
        $this->assertSame(['_ci_context' => ['foo' => 'bar']], $contexts[0]);
    }

    public function testDoesNotLogGlobalContext(): void
    {
        $config                   = new LoggerConfig();
        $config->logGlobalContext = false;

        $logger = new Logger($config);

        Time::setTestNow('2026-02-18 12:00:00');

        service('context')->set('foo', 'bar');

        $expected = 'DEBUG - ' . Time::now()->format('Y-m-d') . ' --> Test message';

        $logger->log('debug', 'Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testDoesNotLogHiddenGlobalContext(): void
    {
        $config                   = new LoggerConfig();
        $config->logGlobalContext = true;

        $logger = new Logger($config);

        Time::setTestNow('2026-02-18 12:00:00');

        service('context')->setHidden('secret', 'hidden value');

        $expected = 'DEBUG - ' . Time::now()->format('Y-m-d') . ' --> Test message';

        $logger->log('debug', 'Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testContextNotPassedToHandlersByDefault(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $logger->log('debug', 'Test message', ['foo' => 'bar', 'baz' => 'qux']);

        $contexts = TestHandler::getContexts();

        $this->assertSame([[]], $contexts);
    }

    public function testLogContextPassesNonInterpolatedKeysToHandlers(): void
    {
        $config             = new LoggerConfig();
        $config->logContext = true;

        $logger = new Logger($config);

        $logger->log('debug', 'Hello {name}', ['name' => 'World', 'user_id' => 42]);

        $contexts = TestHandler::getContexts();

        $this->assertArrayNotHasKey('name', $contexts[0]);
        $this->assertSame(42, $contexts[0]['user_id']);
    }

    public function testLogContextStripsInterpolatedKeysByDefault(): void
    {
        $config             = new LoggerConfig();
        $config->logContext = true;

        $logger = new Logger($config);

        $logger->log('debug', 'Hello {name}', ['name' => 'World']);

        $contexts = TestHandler::getContexts();

        $this->assertSame([[]], $contexts);
    }

    public function testLogContextKeepsInterpolatedKeysWhenEnabled(): void
    {
        $config                     = new LoggerConfig();
        $config->logContext         = true;
        $config->logContextUsedKeys = true;

        $logger = new Logger($config);

        $logger->log('debug', 'Hello {name}', ['name' => 'World']);

        $contexts = TestHandler::getContexts();

        $this->assertArrayHasKey('name', $contexts[0]);
        $this->assertSame('World', $contexts[0]['name']);
    }

    public function testLogContextNormalizesThrowable(): void
    {
        $config             = new LoggerConfig();
        $config->logContext = true;

        $logger = new Logger($config);

        try {
            throw new RuntimeException('Something went wrong', 42);
        } catch (RuntimeException $e) {
            $logger->log('error', 'An error occurred', ['exception' => $e]);
        }

        $contexts = TestHandler::getContexts();

        $this->assertArrayHasKey('exception', $contexts[0]);

        $normalized = $contexts[0]['exception'];

        $this->assertSame(RuntimeException::class, $normalized['class']);
        $this->assertSame('Something went wrong', $normalized['message']);
        $this->assertSame(42, $normalized['code']);
        $this->assertArrayHasKey('file', $normalized);
        $this->assertArrayHasKey('line', $normalized);
        $this->assertArrayNotHasKey('trace', $normalized);
    }

    public function testLogContextDoesNotNormalizeThrowableUnderArbitraryKey(): void
    {
        $config             = new LoggerConfig();
        $config->logContext = true;

        $logger = new Logger($config);

        try {
            throw new RuntimeException('Something went wrong');
        } catch (RuntimeException $e) {
            $logger->log('error', 'An error occurred', ['error' => $e]);
        }

        $contexts = TestHandler::getContexts();

        // Per PSR-3, only the 'exception' key is normalized; other keys are left as-is.
        $this->assertInstanceOf(RuntimeException::class, $contexts[0]['error']);
    }

    public function testLogContextNormalizesThrowableWithTrace(): void
    {
        $config                  = new LoggerConfig();
        $config->logContext      = true;
        $config->logContextTrace = true;

        $logger = new Logger($config);

        try {
            throw new RuntimeException('Something went wrong');
        } catch (RuntimeException $e) {
            $logger->log('error', 'An error occurred', ['exception' => $e]);
        }

        $contexts = TestHandler::getContexts();

        $this->assertArrayHasKey('exception', $contexts[0]);
        $this->assertArrayHasKey('trace', $contexts[0]['exception']);
        $this->assertIsString($contexts[0]['exception']['trace']);
    }

    public function testLogContextNormalizesInterpolatedThrowableWhenUsedKeysEnabled(): void
    {
        $config                     = new LoggerConfig();
        $config->logContext         = true;
        $config->logContextUsedKeys = true;

        $logger = new Logger($config);

        try {
            throw new RuntimeException('Something went wrong');
        } catch (RuntimeException $e) {
            $logger->log('error', '[ERROR] {exception}', ['exception' => $e]);
        }

        $contexts = TestHandler::getContexts();

        $this->assertArrayHasKey('exception', $contexts[0]);

        $normalized = $contexts[0]['exception'];

        $this->assertIsArray($normalized);
        $this->assertSame(RuntimeException::class, $normalized['class']);
        $this->assertSame('Something went wrong', $normalized['message']);
    }

    public function testLogContextDisabledStillAllowsGlobalContext(): void
    {
        $config                   = new LoggerConfig();
        $config->logContext       = false;
        $config->logGlobalContext = true;

        $logger = new Logger($config);

        Time::setTestNow('2026-02-18 12:00:00');

        service('context')->set('request_id', 'abc123');

        $logger->log('debug', 'Test message', ['extra' => 'data']);

        $contexts = TestHandler::getContexts();

        $this->assertArrayNotHasKey('extra', $contexts[0]);
        $this->assertArrayHasKey('_ci_context', $contexts[0]);
        $this->assertSame(['request_id' => 'abc123'], $contexts[0]['_ci_context']);
    }
}
