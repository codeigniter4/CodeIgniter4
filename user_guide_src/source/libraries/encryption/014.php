<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Encryption extends BaseConfig
{
    public string $key = 'hex2bin:your_new_encryption_key_in_hex';

    public array|string $previousKeys = [
        'hex2bin:your_old_encryption_key_in_hex',
        'hex2bin:another_old_key_if_needed',
    ];

    // ... other config options
}
