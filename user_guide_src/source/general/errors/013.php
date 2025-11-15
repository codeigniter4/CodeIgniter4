<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Logger extends BaseConfig
{
    // ...
    // This must contain the log level (5 for LogLevel::WARNING) corresponding to $deprecationLogLevel.
    public $threshold = (ENVIRONMENT === 'production') ? 4 : 9;
    // ...
}
