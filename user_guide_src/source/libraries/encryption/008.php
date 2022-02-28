<?php

class Encryption extends BaseConfig
{
    // In Encryption, you may use
    public $key = 'hex2bin:<your-hex-encoded-key>';

    // or
    public $key = 'base64:<your-base64-encoded-key>';
}
