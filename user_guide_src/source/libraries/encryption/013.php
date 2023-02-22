<?php

use Config\Encryption;
use Config\Services;

$config         = new Encryption();
$config->driver = 'OpenSSL';

// Your CI3's 'encryption_key'
$config->key = hex2bin('64c70b0b8d45b80b9eba60b8b3c8a34d0193223d20fea46f8644b848bf7ce67f');
// Your CI3's 'cipher' and 'mode'
$config->cipher = 'AES-128-CBC';

$config->rawData        = false;
$config->encryptKeyInfo = 'encryption';
$config->authKeyInfo    = 'authentication';

$encrypter = Services::encrypter($config, false);
