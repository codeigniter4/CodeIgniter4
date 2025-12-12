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

use Closure;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Paths;
use Throwable;

/**
 * @see \CodeIgniter\Debug\ExceptionHandlerTest
 */
final class ExceptionHandler extends BaseExceptionHandler implements ExceptionHandlerInterface
{
    use ResponseTrait;

    /**
     * ResponseTrait needs this.
     */
    private ?RequestInterface $request = null;

    /**
     * ResponseTrait needs this.
     */
    private ?ResponseInterface $response = null;

    /**
     * Determines the correct way to display the error.
     *
     * @param CLIRequest|IncomingRequest $request
     */
    public function handle(
        Throwable $exception,
        RequestInterface $request,
        ResponseInterface $response,
        int $statusCode,
        int $exitCode,
    ): void {
        // ResponseTrait needs these properties.
        $this->request  = $request;
        $this->response = $response;

        if ($request instanceof IncomingRequest) {
            try {
                $response->setStatusCode($statusCode);
            } catch (HTTPException) {
                // Workaround for invalid HTTP status code.
                $statusCode = 500;
                $response->setStatusCode($statusCode);
            }

            if (! headers_sent()) {
                header(
                    sprintf(
                        'HTTP/%s %s %s',
                        $request->getProtocolVersion(),
                        $response->getStatusCode(),
                        $response->getReasonPhrase(),
                    ),
                    true,
                    $statusCode,
                );
            }

            // Handles non-HTML requests.
            if (! str_contains($request->getHeaderLine('accept'), 'text/html')) {
                // If display_errors is enabled, shows the error details.
                $data = $this->isDisplayErrorsEnabled()
                    ? $this->collectVars($exception, $statusCode)
                    : '';

                // Sanitize data to remove non-JSON-serializable values (resources, closures)
                // before formatting for API responses (JSON, XML, etc.)
                if ($data !== '') {
                    $data = $this->sanitizeData($data);
                }

                $this->respond($data, $statusCode)->send();

                if (ENVIRONMENT !== 'testing') {
                    // @codeCoverageIgnoreStart
                    exit($exitCode);
                    // @codeCoverageIgnoreEnd
                }

                return;
            }
        }

        // Determine possible directories of error views
        $addPath = ($request instanceof IncomingRequest ? 'html' : 'cli') . DIRECTORY_SEPARATOR;
        $path    = $this->viewPath . $addPath;
        $altPath = rtrim((new Paths())->viewDirectory, '\\/ ')
            . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . $addPath;

        // Determine the views
        $view    = $this->determineView($exception, $path, $statusCode);
        $altView = $this->determineView($exception, $altPath, $statusCode);

        // Check if the view exists
        $viewFile = null;
        if (is_file($path . $view)) {
            $viewFile = $path . $view;
        } elseif (is_file($altPath . $altView)) {
            $viewFile = $altPath . $altView;
        }

        // Displays the HTML or CLI error code.
        $this->render($exception, $statusCode, $viewFile);

        if (ENVIRONMENT !== 'testing') {
            // @codeCoverageIgnoreStart
            exit($exitCode);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Determines the view to display based on the exception thrown, HTTP status
     * code, whether an HTTP or CLI request, etc.
     *
     * @return string The filename of the view file to use
     */
    protected function determineView(
        Throwable $exception,
        string $templatePath,
        int $statusCode = 500,
    ): string {
        // Production environments should have a custom exception file.
        $view = 'production.php';

        if ($this->isDisplayErrorsEnabled()) {
            // If display_errors is enabled, shows the error details.
            $view = 'error_exception.php';
        }

        // 404 Errors
        if ($exception instanceof PageNotFoundException) {
            return 'error_404.php';
        }

        $templatePath = rtrim($templatePath, '\\/ ') . DIRECTORY_SEPARATOR;

        // Allow for custom views based upon the status code
        if (is_file($templatePath . 'error_' . $statusCode . '.php')) {
            return 'error_' . $statusCode . '.php';
        }

        return $view;
    }

    private function isDisplayErrorsEnabled(): bool
    {
        return in_array(
            strtolower(ini_get('display_errors')),
            ['1', 'true', 'on', 'yes'],
            true,
        );
    }

    /**
     * Sanitizes data to remove non-JSON-serializable values like resources and closures.
     * This is necessary for API responses that need to be JSON/XML encoded.
     *
     * @param array<int, bool> $seen Used internally to prevent infinite recursion
     */
    private function sanitizeData(mixed $data, array &$seen = []): mixed
    {
        $type = gettype($data);

        switch ($type) {
            case 'resource':
            case 'resource (closed)':
                return '[Resource #' . (int) $data . ']';

            case 'array':
                $result = [];

                foreach ($data as $key => $value) {
                    $result[$key] = $this->sanitizeData($value, $seen);
                }

                return $result;

            case 'object':
                $oid = spl_object_id($data);
                if (isset($seen[$oid])) {
                    return '[' . $data::class . ' Object *RECURSION*]';
                }
                $seen[$oid] = true;

                if ($data instanceof Closure) {
                    return '[Closure]';
                }

                $result = [];

                foreach ((array) $data as $key => $value) {
                    $cleanKey          = preg_replace('/^\x00.*\x00/', '', (string) $key);
                    $result[$cleanKey] = $this->sanitizeData($value, $seen);
                }

                return $result;

            default:
                return $data;
        }
    }
}
