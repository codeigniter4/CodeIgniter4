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

use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\Log\Exceptions\LogException;
use CodeIgniter\Log\Handlers\HandlerInterface;
use Psr\Log\LoggerInterface;
use Stringable;
use Throwable;

/**
 * The CodeIgntier Logger
 *
 * The message MUST be a string or object implementing __toString().
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data, the only assumption that
 * can be made by implementors is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception".
 *
 * @see \CodeIgniter\Log\LoggerTest
 */
class Logger implements LoggerInterface
{
    /**
     * Used by the logThreshold Config setting to define
     * which errors to show.
     *
     * @var array<string, int>
     */
    protected $logLevels = [
        'emergency' => 1,
        'alert'     => 2,
        'critical'  => 3,
        'error'     => 4,
        'warning'   => 5,
        'notice'    => 6,
        'info'      => 7,
        'debug'     => 8,
    ];

    /**
     * Array of levels to be logged. The rest will be ignored.
     *
     * Set in app/Config/Logger.php
     *
     * @var list<string>
     */
    protected $loggableLevels = [];

    /**
     * File permissions
     *
     * @var int
     */
    protected $filePermissions = 0644;

    /**
     * Format of the timestamp for log files.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Filename Extension
     *
     * @var string
     */
    protected $fileExt;

    /**
     * Caches instances of the handlers.
     *
     * @var array<class-string<HandlerInterface>, HandlerInterface>
     */
    protected $handlers = [];

    /**
     * Holds the configuration for each handler.
     * The key is the handler's class name. The
     * value is an associative array of configuration
     * items.
     *
     * @var array<class-string<HandlerInterface>, array<string, int|list<string>|string>>
     */
    protected $handlerConfig = [];

    /**
     * Caches logging calls for debugbar.
     *
     * @var list<array{level: string, msg: string}>
     */
    public $logCache;

    /**
     * Should we cache our logged items?
     *
     * @var bool
     */
    protected $cacheLogs = false;

    /**
     * Constructor.
     *
     * @param \Config\Logger $config
     *
     * @throws RuntimeException
     */
    public function __construct($config, bool $debug = CI_DEBUG)
    {
        $loggableLevels = is_array($config->threshold) ? $config->threshold : range(1, (int) $config->threshold);

        // Now convert loggable levels to strings.
        // We only use numbers to make the threshold setting convenient for users.
        foreach ($loggableLevels as $level) {
            /** @var false|string $stringLevel */
            $stringLevel = array_search($level, $this->logLevels, true);

            if ($stringLevel === false) {
                continue;
            }

            $this->loggableLevels[] = $stringLevel;
        }

        if (isset($config->dateFormat)) {
            $this->dateFormat = $config->dateFormat;
        }

        if ($config->handlers === []) {
            throw LogException::forNoHandlers('LoggerConfig');
        }

        // Save the handler configuration for later.
        // Instances will be created on demand.
        $this->handlerConfig = $config->handlers;
        $this->cacheLogs     = $debug;

        if ($this->cacheLogs) {
            $this->logCache = [];
        }
    }

    /**
     * System is unusable.
     */
    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     */
    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     */
    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     */
    public function error(string|Stringable $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     */
    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Normal but significant events.
     */
    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     */
    public function info(string|Stringable $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Detailed debug information.
     */
    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        if (is_numeric($level)) {
            $level = array_search((int) $level, $this->logLevels, true);
        }

        if (! array_key_exists($level, $this->logLevels)) {
            throw LogException::forInvalidLogLevel($level);
        }

        if (! in_array($level, $this->loggableLevels, true)) {
            return;
        }

        $message = $this->interpolate($message, $context);

        if ($this->cacheLogs) {
            $this->logCache[] = ['level' => $level, 'msg' => $message];
        }

        foreach ($this->handlerConfig as $className => $config) {
            if (! array_key_exists($className, $this->handlers)) {
                $this->handlers[$className] = new $className($config);
            }

            $handler = $this->handlers[$className];

            if (! $handler->canHandle($level)) {
                continue;
            }

            // If the handler returns false, then we don't execute any other handlers.
            if (! $handler->setDateFormat($this->dateFormat)->handle($level, $message)) {
                break;
            }
        }
    }

    /**
     * Replaces any placeholders in the message with variables
     * from the context, as well as a few special items like:
     *
     * {session_vars}
     * {post_vars}
     * {get_vars}
     * {env}
     * {env:foo}
     * {file}
     * {line}
     *
     * @param string|Stringable    $message
     * @param array<string, mixed> $context
     *
     * @return string
     */
    protected function interpolate($message, array $context = [])
    {
        if (! is_string($message)) {
            return print_r($message, true);
        }

        $replace = [];

        foreach ($context as $key => $val) {
            // Verify that the 'exception' key is actually an exception
            // or error, both of which implement the 'Throwable' interface.
            if ($key === 'exception' && $val instanceof Throwable) {
                $val = $val->getMessage() . ' ' . clean_path($val->getFile()) . ':' . $val->getLine();
            }

            // todo - sanitize input before writing to file?
            $replace['{' . $key . '}'] = $val;
        }

        $replace['{post_vars}'] = '$_POST: ' . print_r($_POST, true);
        $replace['{get_vars}']  = '$_GET: ' . print_r($_GET, true);
        $replace['{env}']       = ENVIRONMENT;

        // Allow us to log the file/line that we are logging from
        if (str_contains($message, '{file}') || str_contains($message, '{line}')) {
            [$file, $line] = $this->determineFile();

            $replace['{file}'] = $file;
            $replace['{line}'] = $line;
        }

        // Match up environment variables in {env:foo} tags.
        if (str_contains($message, 'env:')) {
            preg_match('/env:[^}]+/', $message, $matches);

            foreach ($matches as $str) {
                $key                 = str_replace('env:', '', $str);
                $replace["{{$str}}"] = $_ENV[$key] ?? 'n/a';
            }
        }

        if (isset($_SESSION)) {
            $replace['{session_vars}'] = '$_SESSION: ' . print_r($_SESSION, true);
        }

        return strtr($message, $replace);
    }

    /**
     * Determines the file and line that the logging call
     * was made from by analyzing the backtrace.
     * Find the earliest stack frame that is part of our logging system.
     *
     * @return array{string, int|string}
     */
    public function determineFile(): array
    {
        $logFunctions = [
            'log_message',
            'log',
            'error',
            'debug',
            'info',
            'warning',
            'critical',
            'emergency',
            'alert',
            'notice',
        ];

        $trace = debug_backtrace(0);

        $stackFrames = array_reverse($trace);

        foreach ($stackFrames as $frame) {
            if (in_array($frame['function'], $logFunctions, true)) {
                $file = isset($frame['file']) ? clean_path($frame['file']) : 'unknown';
                $line = $frame['line'] ?? 'unknown';

                return [$file, $line];
            }
        }

        return ['unknown', 'unknown'];
    }
}
