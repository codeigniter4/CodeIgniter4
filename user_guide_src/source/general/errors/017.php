<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

class Exceptions extends BaseConfig
{
    // ...

    public function handler(int $statusCode, Throwable $exception): ExceptionHandlerInterface
    {
        if (in_array($statusCode, [400, 404, 500], true)) {
            return new \App\Libraries\MyExceptionHandler($this);
        }

        if ($exception instanceof PageNotFoundException) {
            return new \App\Libraries\MyExceptionHandler($this);
        }

        return new \CodeIgniter\Debug\ExceptionHandler($this);
    }
}
