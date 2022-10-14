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
 */
final class LoggerTest extends CIUnitTestCase
{
    protected function pregLogEntry(string $level, string $message, bool $same = true): string
    {
        return '|^' . preg_quote($level, '|') . '\s-\s[0-9]{4}-[0-9]{2}-[0-9]{2}\s\-\-\>\s' . preg_quote($message, '|') . ($same ? '$' : '') . '|';
    }

    public function testThrowsExceptionWithBadHandlerSettings()
    {
        $config           = new LoggerConfig();
        $config->handlers = null;

        $this->expectException(FrameworkException::class);
        $this->expectExceptionMessage(lang('Core.noHandlers', ['LoggerConfig']));

        new Logger($config);
    }

    public function testLogThrowsExceptionOnInvalidLevel()
    {
        $config = new LoggerConfig();

        $this->expectException(LogException::class);
        $this->expectExceptionMessage(lang('Log.invalidLogLevel', ['foo']));

        $logger = new Logger($config);

        $logger->log('foo', '');
    }

    public function testLogReturnsFalseWhenLogNotHandled()
    {
        $config            = new LoggerConfig();
        $config->threshold = 3;

        $logger = new Logger($config);

        $this->assertFalse($logger->log('debug', ''));
    }

    public function testLogActuallyLogs()
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = $this->pregLogEntry('DEBUG', 'Test message');
        $logger->log('debug', 'Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testLogDoesnotLogUnhandledLevels()
    {
        $config = new LoggerConfig();

        $config->handlers[TestHandler::class]['handles'] = ['critical'];

        $logger = new Logger($config);

        $logger->log('debug', 'Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(0, $logs);
    }

    public function testLogInterpolatesMessage()
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        $expected = $this->pregLogEntry('DEBUG', 'Test message bar baz');

        $logger->log('debug', 'Test message {foo} {bar}', ['foo' => 'bar', 'bar' => 'baz']);

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testLogInterpolatesPost()
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        $_POST    = ['foo' => 'bar'];
        $expected = $this->pregLogEntry('DEBUG', 'Test message $_POST: ' . print_r($_POST, true));

        $logger->log('debug', 'Test message {post_vars}');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testLogInterpolatesGet()
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        $_GET     = ['bar' => 'baz'];
        $expected = $this->pregLogEntry('DEBUG', 'Test message $_GET: ' . print_r($_GET, true));

        $logger->log('debug', 'Test message {get_vars}');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testLogInterpolatesSession()
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        $_SESSION = ['xxx' => 'yyy'];
        $expected = $this->pregLogEntry('DEBUG', 'Test message $_SESSION: ' . print_r($_SESSION, true));

        $logger->log('debug', 'Test message {session_vars}');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testLogInterpolatesCurrentEnvironment()
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        $expected = $this->pregLogEntry('DEBUG', 'Test message ' . ENVIRONMENT);

        $logger->log('debug', 'Test message {env}');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testLogInterpolatesEnvironmentVars()
    {
        $config = new LoggerConfig();

        $logger = new Logger($config);

        $_ENV['foo'] = 'bar';

        $expected = $this->pregLogEntry('DEBUG', 'Test message bar');

        $logger->log('debug', 'Test message {env:foo}');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testLogInterpolatesFileAndLine()
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

    public function testLogInterpolatesExceptions()
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = $this->pregLogEntry('ERROR', '[ERROR] These are not the droids you are looking for', false);

        try {
            throw new Exception('These are not the droids you are looking for');
        } catch (Exception $e) {
            $logger->log('error', '[ERROR] {exception}', ['exception' => $e]);
        }

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testEmergencyLogsCorrectly()
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = $this->pregLogEntry('EMERGENCY', 'Test message');

        $logger->emergency('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testAlertLogsCorrectly()
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = $this->pregLogEntry('ALERT', 'Test message');

        $logger->alert('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testCriticalLogsCorrectly()
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = $this->pregLogEntry('CRITICAL', 'Test message');

        $logger->critical('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testErrorLogsCorrectly()
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = $this->pregLogEntry('ERROR', 'Test message');

        $logger->error('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testWarningLogsCorrectly()
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = $this->pregLogEntry('WARNING', 'Test message');

        $logger->warning('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testNoticeLogsCorrectly()
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = $this->pregLogEntry('NOTICE', 'Test message');

        $logger->notice('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testInfoLogsCorrectly()
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = $this->pregLogEntry('INFO', 'Test message');

        $logger->info('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testDebugLogsCorrectly()
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = $this->pregLogEntry('DEBUG', 'Test message');

        $logger->debug('Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testLogLevels()
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = $this->pregLogEntry('WARNING', 'Test message');

        $logger->log(5, 'Test message');

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertMatchesRegularExpression($expected, $logs[0]);
    }

    public function testNonStringMessage()
    {
        $config = new LoggerConfig();
        $logger = new Logger($config);

        $expected = '[Tests\Support\Log\Handlers\TestHandler]';
        $logger->log(5, $config);

        $logs = TestHandler::getLogs();

        $this->assertCount(1, $logs);
        $this->assertStringContainsString($expected, $logs[0]);
    }

    public function testDetermineFileNoStackTrace()
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
