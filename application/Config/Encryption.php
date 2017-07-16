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
	  | One of the supported drivers, eg 'openssl' or 'mcrypt'.
	  | The default driver, if you don't specify one, is 'openssl'.
	 */
	public $driver = 'OpenSSL';

	/*
	  |--------------------------------------------------------------------------
	  | Encryption Cipher
	  |--------------------------------------------------------------------------
	  |
	  | Name of the encryption cipher to use, eg 'aes-256' or 'blowfish'
	 */
	public $cipher = 'AES-256-CBC';

	/*
	  |--------------------------------------------------------------------------
	  | Authentication
	  |--------------------------------------------------------------------------
	  |
	  | Use HMAC message authentication (true/false)
	 */
	public $hmac = 'HMAC';

	/*
	  |--------------------------------------------------------------------------
	  | HMAC digest
	  |--------------------------------------------------------------------------
	  |
	  | HMAC digest algorithm to use
	 */
	public $digest = 'SHA512';

	/*
	  |--------------------------------------------------------------------------
	  | Base64 encoding?
	  |--------------------------------------------------------------------------
	  |
	  | If true, base64 encode results, and expect base64-encoded ciphertext.
	 */
	public $base64 = 'base64';

}
