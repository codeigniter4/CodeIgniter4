<?php

namespace App\Libraries;

use CodeIgniter\Debug\BaseExceptionHandler;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class MyExceptionHandler extends BaseExceptionHandler
{
    // You can override the view path.
    protected string $viewPath = APPPATH . 'Views/exception/';

    public function handle(
        Throwable $exception,
        RequestInterface $request,
        ResponseInterface $response,
        int $statusCode,
        int $exitCode
    ) {
        $this->render($exception, $statusCode, $this->viewPath . "error_{$statusCode}.php");

        exit($exitCode);
    }
}
