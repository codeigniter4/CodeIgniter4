<?php

namespace CodeIgniter\Encryption;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
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
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
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
	 * Our configuration
	 */
	protected $config;

	/**
	 * The PHP extension we plan to use
	 *
	 * @var	string
	 */
	protected $handler;

	/**
	 * List of usable handlers (PHP extensions)
	 *
	 * @var	array
	 */
	protected $handlers = [];

	/**
	 * Map of drivers to handler classes, in preference order
	 * 
	 * @var array
	 */
	protected $drivers = [
		'openssl' => 'OpenSSL',
		'mcrypt' => 'Mcrypt',
	];

	/**
	 * Logger instance to record error messages and warnings.
	 * @var \PSR\Log\LoggerInterface
	 */
	protected $logger;

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @param	array	$params	Configuration parameters
	 * @return	void
	 * 
	 * @throws \CodeIgniter\Encryption\EncryptionException
	 */
	public function __construct(array $params = [])
	{
		$this->logger = \Config\Services::logger(true);
		$this->config = new \Config\Encryption();


		if ($params == null)
		// use config if no parameters given
			$params = (array) $this->config;
		else
		// override config with passed parameters
			$params = array_merge((array) $config, $params);

		// determine what is installed
		$this->handlers = [
			'openssl' => extension_loaded('openssl'),
			'mcrypt' => defined('MCRYPT_DEV_URANDOM'),
		];

		if ( ! $this->handlers['mcrypt'] && ! $this->handlers['openssl'])
			throw new EncryptionException('Unable to find an available encryption handler.');

		// how should this be handled?
		$this->driver = $params['driver'] ?? 'OpenSSL';
		$this->handler = $this->drivers[$this->handler];
		$handlerName = 'Handlers\\' . $this->handler . 'Handler';
		$this->encrypter = new $handlerName();

		$this->encrypter->initialize($params);

		// use config key if initialization didn't create one
		if ( ! isset($this->key) && self::strlen($key = $this->config->key) > 0)
			$this->key = $key;

		$this->logger->info('Encryption class Initialized');
	}

// --------------------------------------------------------------------

	/**
	 * Create a random key
	 *
	 * @param	int	$length	Output length
	 * @return	string
	 */
	public static function createKey($length)
	{
		try
		{
			return random_bytes((int) $length);
		} catch (Exception $e)
		{
			throw new EncryptionException('Key creation error: ' . $e->getMessage());
		}

		//FIXME Is this even reachable?
		$is_secure = null;
		$key = openssl_random_pseudo_bytes($length, $is_secure);
		return ($is_secure === true) ? $key : false;
	}

	// --------------------------------------------------------------------

	/**
	 * __get() magic
	 *
	 * @param	string	$key	Property name
	 * @return	mixed
	 */
	public function __get($key)
	{
		// Because aliases
		if ($key === 'mode')
		{
			return array_search($this->mode, $this->modes[$this->handler], true);
		}
		elseif (in_array($key, ['cipher', 'handler', 'handlers', 'digests'], true))
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
