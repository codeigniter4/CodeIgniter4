<?php

echo $encrypter->decrypt($ciphertext);
echo $encrypter->decrypt($ciphertext, ['key' => 'New secret key']);
echo $encrypter->decrypt($ciphertext, ['key' => 'New secret key', 'blockSize' => 32]);
echo $encrypter->decrypt($ciphertext, 'New secret key');
echo $encrypter->decrypt($ciphertext, ['blockSize' => 32]);
