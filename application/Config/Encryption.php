<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Encryption configuration.
 * 
 * These are the settings used for encryption, if you don't pass a parameter
 * array to the encrypter for creation/initialization.
 *
 */
class Encryption extends BaseConfig
{
	/*
	  |--------------------------------------------------------------------------
	  | Encryption Key Starter
	  |--------------------------------------------------------------------------
	  |
	  | If you use the Encryption class you must set an encryption key.
	  | You need to ensure it is long enough for the cipher and mode you plan to use.
	  | See the user guide for more info.
	 */

	public $key = '';

	/*
	  |--------------------------------------------------------------------------
	  | Encryption driver to use
	  |--------------------------------------------------------------------------
	  |
	  | One of the supported drivers, eg 'OpenSSL' or 'Sodium'.
	  | The default driver, if you don't specify one, is 'OpenSSL'.
	 */
	public $driver = 'OpenSSL';

	/*
	  |--------------------------------------------------------------------------
	  | Encryption Cipher
	  |--------------------------------------------------------------------------
	  |
	  | Name of the encryption cipher to use, eg 'aes-256' or 'blowfish'.
	  | The cipher must be supported by your designated driver.
	 */
	public $cipher = 'AES-256-CBC';

	/*
	  |--------------------------------------------------------------------------
	  | Authentication digest
	  |--------------------------------------------------------------------------
	  |
	  | HMAC digest algorithm to use, empty for none.
	  | Values: SHA512, SHA384, SHA256, or SHA224.
	 */
	public $digest = 'SHA512';

	/*
	  |--------------------------------------------------------------------------
	  | Result encoding
	  |--------------------------------------------------------------------------
	  |
	  | Which, if any, encoding to apply to encrypted results and to assume
	  | provided ciphertext.
	  | Values; empty (for no encoding), base64 or hex.
	 */
	public $encoding = 'base64';

}
