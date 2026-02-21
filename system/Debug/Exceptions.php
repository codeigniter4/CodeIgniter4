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

namespace CodeIgniter\Debug;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Exceptions\HasExitCodeInterface;
use CodeIgniter\Exceptions\HTTPExceptionInterface;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Exceptions as ExceptionsConfig;
use ErrorException;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Exceptions manager
 *
 * @see \CodeIgniter\Debug\ExceptionsTest
 */
class Exceptions
{
    use ResponseTrait;

    /**
     * The request.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * The outgoing response.
     *
     * @var ResponseInterface
     */
    protected $response;

    private ?Throwable $exceptionCaughtByExceptionHandler = null;

    public function __construct(
        protected ExceptionsConfig $config,
    ) {
    }

    /**
     * Responsible for registering the error, exception and shutdown
     * handling of our application.
     *
     * @return void
     */
    public function initialize()
    {
        set_exception_handler($this->exceptionHandler(...));
        set_error_handler($this->errorHandler(...));
        register_shutdown_function($this->shutdownHandler(...));
    }

    /**
     * The callback to be registered to `set_exception_handler()`.
     *
     * @return void
     */
    public function exceptionHandler(Throwable $exception)
    {
        $this->exceptionCaughtByExceptionHandler = $exception;

        [$statusCode, $exitCode] = $this->determineCodes($exception);

        $this->request = service('request');

        if ($this->config->log && ! in_array($statusCode, $this->config->ignoreCodes, true)) {
            $uri = $this->request->getPath() === '' ? '/' : $this->request->getPath();

            log_message('critical', "{exClass}: {message}\n{routeInfo}\nin {exFile} on line {exLine}.\n{trace}", [
                'exClass'   => $exception::class,
                'message'   => $exception->getMessage(),
                'routeInfo' => sprintf('[Method: %s, Route: %s]', $this->request->getMethod(), $uri),
                'exFile'    => clean_path($exception->getFile()), // {file} refers to THIS file
                'exLine'    => $exception->getLine(), // {line} refers to THIS line
                'trace'     => render_backtrace($exception->getTrace()),
            ]);

            // Get the first exception.
            $firstException = $exception;

            while (($prevException = $firstException->getPrevious()) instanceof Throwable) {
                $firstException = $prevException;

                log_message('critical', "[Caused by] {exClass}: {message}\nin {exFile} on line {exLine}.\n{trace}", [
                    'exClass' => $prevException::class,
                    'message' => $prevException->getMessage(),
                    'exFile'  => clean_path($prevException->getFile()), // {file} refers to THIS file
                    'exLine'  => $prevException->getLine(), // {line} refers to THIS line
                    'trace'   => render_backtrace($prevException->getTrace()),
                ]);
            }
        }

        $this->response = service('response');

        $handler = $this->config->handler($statusCode, $exception);
        $handler->handle($exception, $this->request, $this->response, $statusCode, $exitCode);
    }

    /**
     * The callback to be registered to `set_error_handler()`.
     *
     * @return bool
     *
     * @throws ErrorException
     */
    public function errorHandler(int $severity, string $message, ?string $file = null, ?int $line = null)
    {
        if ($this->isDeprecationError($severity)) {
            if ($this->isSessionSidDeprecationError($message, $file, $line)) {
                return true;
            }

            if (! $this->config->logDeprecations || (bool) env('CODEIGNITER_SCREAM_DEPRECATIONS')) {
                throw new ErrorException($message, 0, $severity, $file, $line);
            }

            return $this->handleDeprecationError($message, $file, $line);
        }

        if ((error_reporting() & $severity) !== 0) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        }

        return false; // return false to propagate the error to PHP standard error handler
    }

    /**
     * Checks to see if any errors have happened during shutdown that
     * need to be caught and handle them.
     *
     * @return void
     */
    public function shutdownHandler()
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        ['type' => $type, 'message' => $message, 'file' => $file, 'line' => $line] = $error;

        if ($this->exceptionCaughtByExceptionHandler instanceof Throwable) {
            $message .= "\n【Previous Exception】\n"
                . $this->exceptionCaughtByExceptionHandler::class . "\n"
                . $this->exceptionCaughtByExceptionHandler->getMessage() . "\n"
                . $this->exceptionCaughtByExceptionHandler->getTraceAsString();
        }

        if (in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
            $this->exceptionHandler(new ErrorException($message, 0, $type, $file, $line));
        }
    }

    /**
     * Handles session.sid_length and session.sid_bits_per_character deprecations in PHP 8.4.
     */
    private function isSessionSidDeprecationError(string $message, ?string $file = null, ?int $line = null): bool
    {
        if (PHP_VERSION_ID >= 80400 && str_contains($message, 'session.sid_')) {
            log_message(
                LogLevel::WARNING,
                '[DEPRECATED] {message} in {errFile} on line {errLine}.',
                [
                    'message' => $message,
                    'errFile' => clean_path($file ?? ''),
                    'errLine' => $line ?? 0,
                ],
            );

            return true;
        }

        return false;
    }

    /**
     * Determines the HTTP status code and the exit status code for this request.
     */
    protected function determineCodes(Throwable $exception): array
    {
        $statusCode = 500;
        $exitStatus = EXIT_ERROR;

        if ($exception instanceof HTTPExceptionInterface) {
            $statusCode = $exception->getCode();
        }

        if ($exception instanceof HasExitCodeInterface) {
            $exitStatus = $exception->getExitCode();
        }

        return [$statusCode, $exitStatus];
    }

    private function isDeprecationError(int $error): bool
    {
        $deprecations = E_DEPRECATED | E_USER_DEPRECATED;

        return ($error & $deprecations) !== 0;
    }

    private function handleDeprecationError(string $message, ?string $file = null, ?int $line = null): true
    {
        // Remove the trace of the error handler.
        $trace = array_slice(debug_backtrace(), 2);

        log_message(
            $this->config->deprecationLogLevel,
            "[DEPRECATED] {message} in {errFile} on line {errLine}.\n{trace}",
            [
                'message' => $message,
                'errFile' => clean_path($file ?? ''),
                'errLine' => $line ?? 0,
                'trace'   => render_backtrace($trace),
            ],
        );

        return true;
    }
}
