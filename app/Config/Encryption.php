<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Encryption configuration.
 *
 * These are the settings used for encryption, if you don't pass a parameter
 * array to the encrypter for creation/initialization.
 */
class Encryption extends BaseConfig
{
	/**
	 * --------------------------------------------------------------------------
	 * Encryption Key Starter
	 * --------------------------------------------------------------------------
	 *
	 * If you use the Encryption class you must set an encryption key (seed).
	 * You need to ensure it is long enough for the cipher and mode you plan to use.
	 * See the user guide for more info.
	 *
	 * @var string
	 */
	public $key = '';

	/**
	 * --------------------------------------------------------------------------
	 * Encryption Driver to Use
	 * --------------------------------------------------------------------------
	 *
	 * One of the supported drivers, e.g. 'OpenSSL' or 'Sodium'.
	 *
	 * @var string
	 */
	public $driver = 'OpenSSL';

	/**
	 * --------------------------------------------------------------------------
	 * SodiumHandler's Padding Size
	 * --------------------------------------------------------------------------
	 *
	 * This is the number of bytes that will be padded to the plaintext message
	 * before it is encrypted. Maximum allowed value is 512 bytes. If none is
	 * given, it will default to 512.
	 *
	 * @var integer
	 */
	public $blockSize = 512;

	/**
	 * --------------------------------------------------------------------------
	 * Encryption digest
	 * --------------------------------------------------------------------------
	 *
	 * HMAC digest to use, e.g. 'SHA512' or 'SHA256'. Default value is 'SHA512'.
	 *
	 * @var string
	 */
	public $digest = 'SHA512';
}
