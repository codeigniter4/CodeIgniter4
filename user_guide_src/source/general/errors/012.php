<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use Psr\Log\LogLevel;

class Exceptions extends BaseConfig
{
    // ...
    public bool $logDeprecations = true; // If set to false, an exception will be thrown.
    // ...
    public string $deprecationLogLevel = LogLevel::WARNING; // This should be one of the log levels supported by PSR-3.
    // ...
}
