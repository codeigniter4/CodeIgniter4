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
use CodeIgniter\Boot;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Debug\Inspection\Frames;
use CodeIgniter\Debug\Inspection\Inspector;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Paths;
use Override;
use Throwable;

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
    #[Override]
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
                    ? $this->collectExceptionDetails($exception, $statusCode) + ['trace' => $this->apiTrace($exception)]
                    : '';

                $this->respond($data, $statusCode)->send();

                if ($this->shouldExit()) {
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
        if ($request instanceof IncomingRequest) {
            $this->renderWithViewData($exception, $statusCode, $viewFile, [
                'request'  => $this->request,
                'response' => $this->response,
            ]);
        } else {
            CLI::redirectToStderr(fn () => $this->render($exception, $statusCode, $viewFile));
        }

        if ($this->shouldExit()) {
            // @codeCoverageIgnoreStart
            exit($exitCode);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Whether to end the script after the error is displayed.
     *
     * In worker mode, the worker loop must keep running, so the
     * handler returns and the worker cleans up after the request.
     */
    private function shouldExit(): bool
    {
        return ! service('environment')->isTesting() && ! Boot::isWorkerMode();
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
     * Return only scalar frame metadata suitable for serialization.
     *
     * @return list<array{file?: string, line?: int, class?: string, type?: string, function: string}>
     */
    private function apiTrace(Throwable $exception): array
    {
        if (! defined('SHOW_DEBUG_BACKTRACE') || ! SHOW_DEBUG_BACKTRACE) {
            return [];
        }

        $trace = [];

        foreach (Frames::fromException((new Inspector($exception))->rootCause()) as $frame) {
            $trace[] = $frame->metadata();
        }

        return $trace;
    }
}
