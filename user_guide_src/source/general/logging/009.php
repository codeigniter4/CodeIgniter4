<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Logger extends BaseConfig
{
    // Enable passing non-interpolated context keys to handlers as structured data.
    public bool $logContext = true;

    // Include the stack trace when a Throwable is found in the context.
    public bool $logContextTrace = true;

    // Also pass context keys that were used as {placeholder} in the message.
    public bool $logContextUsedKeys = false;
}
