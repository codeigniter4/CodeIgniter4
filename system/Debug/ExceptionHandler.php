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

use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Paths;
use Throwable;

class ExceptionHandler extends BaseExceptionHandler
{
    /**
     * Determines the correct way to display the error.
     *
     * @return mixed|void
     */
    public function handle()
    {
        if (! is_cli()) {
            $this->response->setStatusCode($this->statusCode);
            header(sprintf('HTTP/%s %s %s', $this->request->getProtocolVersion(), $this->response->getStatusCode(), $this->response->getReasonPhrase()), true, $this->statusCode);

            // Display a JSON/XML response if we weren't asked for HTML
            if (strpos($this->request->getHeaderLine('accept'), 'text/html') === false) {
                $this->respond(ENVIRONMENT === 'development' ? $this->collectVars($this->exception, $this->statusCode) : '', $this->statusCode)->send();

                exit($this->exitCode);
            }
        }

        // Determine possible directories of error views
        $path    = $this->viewPath;
        $altPath = rtrim((new Paths())->viewDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR;

        $path    .= (is_cli() ? 'cli' : 'html') . DIRECTORY_SEPARATOR;
        $altPath .= (is_cli() ? 'cli' : 'html') . DIRECTORY_SEPARATOR;

        // Determine the views
        $view    = $this->determineView($this->exception, $path);
        $altView = $this->determineView($this->exception, $altPath);

        // Check if the view exists
        $viewFile = null;
        if (is_file($path . $view)) {
            $viewFile = $path . $view;
        } elseif (is_file($altPath . $altView)) {
            $viewFile = $altPath . $altView;
        }

        // Displays the HTML or CLI error code.
        $this->render($viewFile);

        exit($this->exitCode);
    }

    /**
     * Determines the view to display based on the exception thrown,
     * whether an HTTP or CLI request, etc.
     *
     * @return string The path and filename of the view file to use
     */
    private function determineView(Throwable $exception, string $templatePath): string
    {
        // Production environments should have a custom exception file.
        $view         = 'production.php';
        $templatePath = rtrim($templatePath, '\\/ ') . DIRECTORY_SEPARATOR;

        if (str_ireplace(['off', 'none', 'no', 'false', 'null'], '', ini_get('display_errors'))) {
            $view = 'error_exception.php';
        }

        // 404 Errors
        if ($exception instanceof PageNotFoundException) {
            return 'error_404.php';
        }

        // Allow for custom views based upon the status code
        if (is_file($templatePath . 'error_' . $exception->getCode() . '.php')) {
            return 'error_' . $exception->getCode() . '.php';
        }

        return $view;
    }
}
