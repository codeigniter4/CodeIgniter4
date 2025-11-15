<?php

$plainText  = 'This is a plain-text message!';
$ciphertext = $encrypter->encrypt($plainText);

// Outputs: This is a plain-text message!
echo $encrypter->decrypt($ciphertext);
