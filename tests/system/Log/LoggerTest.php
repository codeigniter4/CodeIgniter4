<?php

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
use CodeIgniter\Log\Exceptions\LogException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockLogger as LoggerConfig;
use Exception;
use Tests\Support\Log\Handlers\TestHandler;

/**
 * @internal
 *
 * @group Others
 */
final class LoggerTest extends CIUnitTestCase
{
    public function testThrowsExceptionWithBadHandlerSettings(): void
    {
        $config           = new LoggerConfig();
        $config->handlers = null;

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

    public function testLogReturnsFalseWhenLogNotHandled(): void
    {
        $config            = new LoggerConfig();
        $config->threshold = 3;

        $logger = new Logger($config);

        $this->assertFalse($logger->log('debug', ''));
    }

    public function testLogActuallyLogs(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = 'DEBUG - ' . date('Y-m-d') . ' --> Test message';
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

        $expected = 'DEBUG - ' . date('Y-m-d') . ' --> Test message bar baz';

        $logger->log('debug', 'Test message {foo} {bar}', ['foo' => 'bar', 'bar' => 'baz']);

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testLogInterpolatesPost(): void
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        $_POST    = ['foo' => 'bar'];
        $expected = 'DEBUG - ' . date('Y-m-d') . ' --> Test message $_POST: ' . print_r($_POST, true);

        $logger->log('debug', 'Test message {post_vars}');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testLogInterpolatesGet(): void
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        $_GET     = ['bar' => 'baz'];
        $expected = 'DEBUG - ' . date('Y-m-d') . ' --> Test message $_GET: ' . print_r($_GET, true);

        $logger->log('debug', 'Test message {get_vars}');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testLogInterpolatesSession(): void
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        $_SESSION = ['xxx' => 'yyy'];
        $expected = 'DEBUG - ' . date('Y-m-d') . ' --> Test message $_SESSION: ' . print_r($_SESSION, true);

        $logger->log('debug', 'Test message {session_vars}');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testLogInterpolatesCurrentEnvironment(): void
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        $expected = 'DEBUG - ' . date('Y-m-d') . ' --> Test message ' . ENVIRONMENT;

        $logger->log('debug', 'Test message {env}');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testLogInterpolatesEnvironmentVars(): void
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        $_ENV['foo'] = 'bar';

        $expected = 'DEBUG - ' . date('Y-m-d') . ' --> Test message bar';

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

    public function testLogInterpolatesExceptions(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = 'ERROR - ' . date('Y-m-d') . ' --> [ERROR] These are not the droids you are looking for';

        try {
            throw new Exception('These are not the droids you are looking for');
        } catch (Exception $e) {
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

        $expected = 'EMERGENCY - ' . date('Y-m-d') . ' --> Test message';

        $logger->emergency('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testAlertLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = 'ALERT - ' . date('Y-m-d') . ' --> Test message';

        $logger->alert('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testCriticalLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = 'CRITICAL - ' . date('Y-m-d') . ' --> Test message';

        $logger->critical('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testErrorLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = 'ERROR - ' . date('Y-m-d') . ' --> Test message';

        $logger->error('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testWarningLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = 'WARNING - ' . date('Y-m-d') . ' --> Test message';

        $logger->warning('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testNoticeLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = 'NOTICE - ' . date('Y-m-d') . ' --> Test message';

        $logger->notice('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testInfoLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = 'INFO - ' . date('Y-m-d') . ' --> Test message';

        $logger->info('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testDebugLogsCorrectly(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = 'DEBUG - ' . date('Y-m-d') . ' --> Test message';

        $logger->debug('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testLogLevels(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = 'WARNING - ' . date('Y-m-d') . ' --> Test message';

        $logger->log(5, 'Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertSame($expected, $logs[0]);
    }

    public function testNonStringMessage(): void
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = '[Tests\Support\Log\Handlers\TestHandler]';
        $logger->log(5, $config);

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertStringContainsString($expected, $logs[0]);
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
}
