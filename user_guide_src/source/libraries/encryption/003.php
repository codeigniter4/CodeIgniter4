<?php

$config         = new \Config\Encryption();
$config->key    = 'aBigsecret_ofAtleast32Characters';
$config->driver = 'OpenSSL';

$encrypter = \Config\Services::encrypter($config);
