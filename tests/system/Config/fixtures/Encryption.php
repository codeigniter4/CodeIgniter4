<?php

use Config\Encryption as EncryptionConfig;

class Encryption extends EncryptionConfig
{
    private const HEX2BIN = 'hex2bin:84cf2c0811d5daf9e1c897825a3debce91f9a33391e639f72f7a4740b30675a2';
    private const BASE64  = 'base64:Psf8bUHRh1UJYG2M7e+5ec3MdjpKpzAr0twamcAvOcI=';
    public $key;
    public $driver = 'MCrypt';

    public function __construct(string $prefix = 'hex2bin')
    {
        if ($prefix === 'base64') {
            $this->key = self::BASE64;
        } else {
            $this->key = self::HEX2BIN;
        }

        parent::__construct();
    }
}
