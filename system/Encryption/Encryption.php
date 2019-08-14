<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Encryption;

use Config\Encryption as EncryptionConfig;
use CodeIgniter\Encryption\Exceptions\EncryptionException;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Config\Services;

/**
 * CodeIgniter Encryption Manager
 *
 * Provides two-way keyed encryption via PHP's Sodium and/or OpenSSL extensions.
 * This class determines the driver, cipher, and mode to use, and then
 * initializes the appropriate encryption handler.
 */
class Encryption
{

	/**
	 * The encrypter we create
	 *
	 * @var string
	 */
	protected $encrypter;

	/**
	 * The driver being used
	 */
	protected $driver;

	/**
	 * The key/seed being used
	 */
	protected $key;

	 /**
	  * The derived hmac key
	  */
	protected $hmacKey;

	/**
	 * HMAC digest to use
	 */
	protected $digest = 'SHA512';

	/**
	 * Map of drivers to handler classes, in preference order
	 *
	 * @var array
	 */
	protected $drivers = [
		'OpenSSL',
	];

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @param  BaseConfig $config Configuration parameters
	 * @return void
	 *
	 * @throws \CodeIgniter\Encryption\Exceptions\EncryptionException
	 */
	public function __construct(BaseConfig $config = null)
	{
		if (empty($config))
		{
			$config = new \Config\Encryption();
		}
		$this->driver = $config->driver;
		$this->key    = $config->key;

		// determine what is installed
		$this->handlers = [
			'OpenSSL' => extension_loaded('openssl'),
		];

		if (! in_array(true, $this->handlers))
		{
			throw EncryptionException::forNoHandlerAvailable();
		}
	}

	/**
	 * Initialize or re-initialize an encrypter
	 *
	 * @param  BaseConfig $config Configuration parameters
	 * @return \CodeIgniter\Encryption\EncrypterInterface
	 *
	 * @throws \CodeIgniter\Encryption\Exceptions\EncryptionException
	 */
	public function initialize(BaseConfig $config = null)
	{
		if (empty($config))
		{
			$config = new \Config\Encryption();
		}
		$this->driver = $config->driver;
		$this->key    = $config->key;

		// Insist on a driver
		if (! isset($params['driver']))
		{
			throw EncryptionException::forNoDriverRequested();
		}

		// Check for an unknown driver
		if (! in_array($params['driver'], $this->drivers))
		{
			throw EncryptionException::forUnKnownHandler($params['driver']);
		}

		// Check for an unavailable driver
		if (! $this->handlers[$params['driver']])
		{
			throw EncryptionException::forDriverNotAvailable($params['driver']);
		}

		// Derive a secret key for the encrypter
			$this->hmacKey = bin2hex(\hash_hkdf($this->digest, $this->key));

		$handlerName     = 'CodeIgniter\\Encryption\\Handlers\\' . $this->driver . 'Handler';
		$this->encrypter = new $handlerName($config);
		return $this->encrypter;
	}

	// --------------------------------------------------------------------

	/**
	 * Create a random key
	 *
	 * @param  integer $length Output length
	 * @return string
	 */
	public static function createKey($length = 32)
	{
		return random_bytes($length);
	}

	// --------------------------------------------------------------------

	/**
	 * __get() magic, providing readonly access to some of our protected properties
	 *
	 * @param  string $key Property name
	 * @return mixed
	 */
	public function __get($key)
	{
		if (in_array($key, ['config', 'key', 'driver', 'drivers', 'default'], true))
		{
			return $this->{$key};
		}

		return null;
	}

	// --------------------------------------------------------------------

	/**
	 * Byte-safe strlen()
	 *
	 * @param  string $str
	 * @return integer
	 */
	protected static function strlen($str)
	{
		return mb_strlen($str, '8bit');
	}

	// --------------------------------------------------------------------

	/**
	 * Byte-safe substr()
	 *
	 * @param  string  $str
	 * @param  integer $start
	 * @param  integer $length
	 * @return string
	 */
	protected static function substr($str, $start, $length = null)
	{
		return mb_substr($str, $start, $length, '8bit');
	}

}
