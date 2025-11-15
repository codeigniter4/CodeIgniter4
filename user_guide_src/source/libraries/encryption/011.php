<?php

$ciphertext = $encrypter->encrypt('My secret message');
$ciphertext = $encrypter->encrypt('My secret message', ['key' => 'New secret key']);
$ciphertext = $encrypter->encrypt('My secret message', ['key' => 'New secret key', 'blockSize' => 32]);
$ciphertext = $encrypter->encrypt('My secret message', 'New secret key');
$ciphertext = $encrypter->encrypt('My secret message', ['blockSize' => 32]);
