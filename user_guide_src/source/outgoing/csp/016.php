<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ContentSecurityPolicy extends BaseConfig
{
    // ...

    public bool $enableStyleNonce = false;

    public bool $enableScriptNonce = false;
}
