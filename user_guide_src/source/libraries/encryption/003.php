<?php

use Config\Encryption;

$config         = config(Encryption::class);
$config->key    = 'aBigsecret_ofAtleast32Characters';
$config->driver = 'OpenSSL';

$encrypter = service('encrypter', $config);
