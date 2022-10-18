<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

use Throwable;

class Exceptions extends BaseConfig
{
    // ...

    public function handler(int $statusCode, Throwable $exception)
    {
        return new \CodeIgniter\Debug\ExceptionHandler($this);
    }
}
