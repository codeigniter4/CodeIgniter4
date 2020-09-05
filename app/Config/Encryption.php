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
	 * The default driver, if you don't specify one, is 'OpenSSL'.
	 *
	 * @var string
	 */
	public $driver = 'OpenSSL';

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
