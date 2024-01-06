<?php

// Get a hex-encoded representation of the key:
$encoded = bin2hex(\CodeIgniter\Encryption\Encryption::createKey(32));

// Put the same value with hex2bin(),
// so that it is still passed as binary to the library:
$key = hex2bin('your-hex-encoded-key');
