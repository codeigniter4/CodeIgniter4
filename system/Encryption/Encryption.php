<?php namespace CodeIgniter\Encryption;

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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use Psr\Log\LoggerAwareTrait;

/**
 * Encryption exception
 *
 */
class EncryptionException extends \Exception
{

}

/**
 * CodeIgniter Encryption Manager
 *
 * Provides two-way keyed encryption via PHP's MCrypt and/or OpenSSL extensions.
 * This class determines the driver, cipher, and mode to use, and then
 * initializes the appropriate encryption handler.
 */
class Encryption
{

	use LoggerAwareTrait;

	/**
	 * The encrypter we create
	 *
	 * @var	string
	 */
	protected $encrypter;

	/**
	 * Our remembered configuration
	 */
	protected $config = [];

	/**
	 * Logger instance to record error messages and warnings.
	 * @var \PSR\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * Our default configuration
	 */
	protected $default = [
		'driver'	 => 'OpenSSL', // The PHP extension we plan to use
		'key'		 => '', // no starting key material
		'cipher'	 => 'AES-256-CBC', // Encryption cipher
		'digest'	 => 'SHA512', // HMAC digest algorithm to use
		'encoding'	 => 'base64', // Base64 encoding
	];
	protected $driver, $key, $cipher, $digest, $base64;

	/**
	 * Map of drivers to handler classes, in preference order
	 *
	 * @var array
	 */
	protected $drivers = [
		'OpenSSL',
	];

	/**
	 * List of supported HMAC algorithms
	 *
	 * name => digest size pairs
	 *
	 * @var	array
	 */
	protected $digests = [
		'SHA224' => 28,
		'SHA256' => 32,
		'SHA384' => 48,
		'SHA512' => 64
	];

	/**
	 * List of acceptable encodings
	 */
	protected $encodings = ['base64', 'hex'];

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @param	mixed	$params	Configuration parameters
	 * @return	void
	 *
	 * @throws \CodeIgniter\Encryption\EncryptionException
	 */
	public function __construct($params = [])
	{
		$this->logger = \Config\Services::logger(true);
		$this->config = array_merge($this->default, (array) new \Config\Encryption());

		$params = $this->properParams($params);

		// Check for an unknown driver
		if (isset($this->drivers[$params['driver']]))
			throw new EncryptionException("Unknown handler '" . $params['driver'] . "' cannot be configured.");

		// determine what is installed
		$this->handlers = [
			'OpenSSL' => extension_loaded('openssl'),
		];

		if ( ! in_array(true, $this->handlers))
			throw new EncryptionException('Unable to find an available encryption handler.');

		$this->logger->info('Encryption class Initialized');
	}

	/**
	 * Initialize or re-initialize an encrypter
	 *
	 * @param	array	$params	Configuration parameters
	 * @return	\CodeIgniter\Encryption\EncrypterInterface
	 *
	 * @throws \CodeIgniter\Encryption\EncryptionException
	 */
	public function initialize(array $params = [])
	{
		$params = $this->properParams($params);

		// Insist on a driver
		if ( ! isset($params['driver']))
			throw new EncryptionException("No driver requested; Miss Daisy will be so upset!");

		// Check for an unknown driver
		if ( ! in_array($params['driver'], $this->drivers))
			throw new EncryptionException("Unknown handler '" . $params['driver'] . "' cannot be configured.");

		// Check for an unavailable driver
		if ( ! $this->handlers[$params['driver']])
			throw new EncryptionException("Driver '" . $params['driver'] . "' is not available.");

		// Check for a bad digest
		if ( ! empty($params['digest']))
			if ( ! isset($this->digests[$params['digest']]))
				throw new EncryptionException("Unknown digest '" . $params['digest'] . "' specified.");

		// Check for valid encoding
		if ( ! empty($param['encoding']))
			if ( ! in_array($params['encoding'], $this->encodings))
				throw new EncryptionException("Unknown encoding '" . $params['encoding'] . "' specified.");

		// Derive a secret key for the encrypter
		$hmacKey = strcmp(phpversion(), '7.1.2') >= 0 ? \hash_hkdf($this->digest, $params['key']) : $this->hkdf($params['key'], $this->digest);
		$params['secret'] = bin2hex($hmacKey);

		$handlerName = 'CodeIgniter\\Encryption\\Handlers\\' . $this->driver . 'Handler';
		$this->encrypter = new $handlerName($params);
		return $this->encrypter;
	}

	/**
	 * Determine proper parameters
	 *
	 * @param array|object $params
	 *
	 * @return array|null
	 */
	protected function properParams($params = null)
	{
		// use existing config if no parameters given
		if (empty($params))
			$params = $this->config;

		// treat the paramater as a Config object?
		if (is_object($params))
			$params = (array) $params;

		// Capitalize cipher & digest
		if (isset($params['cipher']))
			$params['cipher'] = strtoupper($params['cipher']);
		if (isset($params['digest']))
			$params['digest'] = strtoupper($params['digest']);

		// override base config with passed parameters
		$params = array_merge($this->config, $params);
		// make sure we only have expected parameters
		$params = array_intersect_key($params, $this->default);

		// and remember what we are up to
		$this->config = $params;

		// make the parameters conveniently accessible
		foreach ($params as $pkey => $value)
			$this->$pkey = $value;

		return $params;
	}

// --------------------------------------------------------------------

	/**
	 * Create a random key
	 *
	 * @param	int	$length	Output length
	 * @return	string
	 */
	public static function createKey($length = 32)
	{
		return \openssl_random_pseudo_bytes($length);
	}

	// --------------------------------------------------------------------

	/**
	 * __get() magic, providing readonly access to some of our protected properties
	 *
	 * @param	string	$key	Property name
	 * @return	mixed
	 */
	public function __get($key)
	{
		if (in_array($key, ['config', 'cipher', 'key', 'driver', 'drivers', 'digest', 'digests', 'default', 'encoding'], true))
		{
			return $this->{$key};
		}

		return null;
	}

	// --------------------------------------------------------------------

	/**
	 * Byte-safe strlen()
	 *
	 * @param	string	$str
	 * @return	int
	 */
	protected static function strlen($str)
	{
		return mb_strlen($str, '8bit');
	}

	// --------------------------------------------------------------------

	/**
	 * HKDF legacy implementation, from CodeIgniter3.
	 *
	 * Fallback if PHP version < 7.1.2
	 *
	 * @link    https://tools.ietf.org/rfc/rfc5869.txt
	 *
	 * @param    string $key    Input key
	 * @param    string $digest A SHA-2 hashing algorithm
	 * @param    string $salt   Optional salt
	 * @param    int    $length Output length (defaults to the selected digest size)
	 * @param    string $info   Optional context/application-specific info
	 *
	 * @return    string    A pseudo-random key
	 */
	public function hkdf($key, $digest = 'sha512', $salt = null, $length = null, $info = '')
	{
		if ( ! isset($this->digests[$digest]))
		{
			return false;
		}

		if (empty($length) OR ! is_int($length))
		{
			$length = $this->digests[$digest];
		}
		elseif ($length > (255 * $this->digests[$digest]))
		{
			return false;
		}

		self::strlen($salt) OR $salt = str_repeat("\0", $this->digests[$digest]);

		$prk = hash_hmac($digest, $key, $salt, true);
		$key = '';
		for ($key_block = '', $block_index = 1; self::strlen($key) < $length; $block_index ++)
		{
			$key_block = hash_hmac($digest, $key_block . $info . chr($block_index), $prk, true);
			$key .= $key_block;
		}

		return self::substr($key, 0, $length);
	}

	// --------------------------------------------------------------------

	/**
	 * Byte-safe substr()
	 *
	 * @param	string	$str
	 * @param	int	$start
	 * @param	int	$length
	 * @return	string
	 */
	protected static function substr($str, $start, $length = null)
	{
		return mb_substr($str, $start, $length, '8bit');
	}

}
