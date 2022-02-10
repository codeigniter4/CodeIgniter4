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

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use Config\Exceptions as ExceptionsConfig;
use ErrorException;
use Throwable;

/**
 * Exceptions manager
 */
class Exceptions
{
    /**
     * Config for debug exceptions.
     *
     * @var ExceptionsConfig
     */
    protected $config;

    /**
     * The incoming request.
     *
     * @var IncomingRequest
     */
    protected $request;

    /**
     * The outgoing response.
     *
     * @var Response
     */
    protected $response;

    public function __construct(ExceptionsConfig $config, IncomingRequest $request, Response $response)
    {
        $this->config   = $config;
        $this->request  = $request;
        $this->response = $response;

        // workaround for upgraded users
        if (! isset($this->config->sensitiveDataInTrace)) {
            $this->config->sensitiveDataInTrace = [];
        }
    }

    /**
     * Responsible for registering the error, exception and shutdown
     * handling of our application.
     *
     * @codeCoverageIgnore
     */
    public function initialize()
    {
        set_exception_handler([$this, 'exceptionHandler']);
        set_error_handler([$this, 'errorHandler']);
        register_shutdown_function([$this, 'shutdownHandler']);
    }

    /**
     * Catches any uncaught errors and exceptions, including most Fatal errors
     * (Yay PHP7!). Will log the error, display it if display_errors is on,
     * and fire an event that allows custom actions to be taken at this point.
     *
     * @codeCoverageIgnore
     */
    public function exceptionHandler(Throwable $exception)
    {
        [$statusCode, $exitCode] = $this->determineCodes($exception);

        if ($this->config->log === true && ! in_array($statusCode, $this->config->ignoreCodes, true)) {
            log_message('critical', $exception->getMessage() . "\n{trace}", [
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        $config = config('Exceptions');

        if (! method_exists($config, 'handler')) {
            exit('Config\Exception must have a handler() method.');
        }

        $handler = config('Exceptions')->handler($statusCode, $exception);

        if (! $handler instanceof BaseExceptionHandler) {
            exit('Exception Handler not found.');
        }

        $handler
            ->setException($exception)
            ->setStatusCode($statusCode)
            ->setExitCode($exitCode)
            ->setRequest($this->request)
            ->setResponse($this->response)
            ->handle();
    }

    /**
     * Even in PHP7, some errors make it through to the errorHandler, so
     * convert these to Exceptions and let the exception handler log it and
     * display it.
     *
     * This seems to be primarily when a user triggers it with trigger_error().
     *
     * @throws ErrorException
     *
     * @codeCoverageIgnore
     */
    public function errorHandler(int $severity, string $message, ?string $file = null, ?int $line = null)
    {
        if (! (error_reporting() & $severity)) {
            return;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Checks to see if any errors have happened during shutdown that
     * need to be caught and handle them.
     *
     * @codeCoverageIgnore
     */
    public function shutdownHandler()
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        ['type' => $type, 'message' => $message, 'file' => $file, 'line' => $line] = $error;

        if (in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
            $this->exceptionHandler(new ErrorException($message, $type, 0, $file, $line));
        }
    }

    /**
     * Determines the HTTP status code and the exit status code for this request.
     */
    protected function determineCodes(Throwable $exception): array
    {
        $statusCode = abs($exception->getCode());

        if ($statusCode < 100 || $statusCode > 599) {
            $exitStatus = $statusCode + EXIT__AUTO_MIN;

            if ($exitStatus > EXIT__AUTO_MAX) {
                $exitStatus = EXIT_ERROR;
            }

            $statusCode = 500;
        } else {
            $exitStatus = EXIT_ERROR;
        }

        return [$statusCode, $exitStatus];
    }
}
