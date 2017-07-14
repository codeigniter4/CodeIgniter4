<?php namespace CodeIgniter\Encryption\Handlers;

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
use CodeIgniter\Config\BaseConfig;
use Psr\Log\LoggerAwareTrait;

/**
 * Base class for session handling
 */
abstract class BaseHandler implements \CodeIgniter\Encryption\EncrypterInterface
{

	use LoggerAwareTrait;

	/**
	 * Encryption cipher
	 *
	 * @var	string
	 */
	protected $cipher = 'aes-256-cbc';

	/**
	 * Cipher handler
	 *
	 * @var	mixed
	 */
	protected $handler;

	/**
	 * Encryption key (starting point, anyway)
	 *
	 * @var	string
	 */
	protected $key;

	/**
	 * Derived secret key
	 *
	 * @var	string
	 */
	protected $secret;

	/**
	 * Derived HMAC digest
	 *
	 * @var	string
	 */
	protected $digest;

	
	/**
	 * List of supported HMAC algorithms
	 *
	 * name => digest size pairs
	 *
	 * @var	array
	 */
	protected $digests = [
		'sha224' => 28,
		'sha256' => 32,
		'sha384' => 48,
		'sha512' => 64
	];

	/**
	 * Logger instance to record error messages and warnings.
	 * @var \PSR\Log\LoggerInterface
	 */
	protected $logger;

//--------------------------------------------------------------------

	/**
	 * Constructor
	 * @param BaseConfig $config
	 */
	public function __construct($config = null)
	{
		$this->logger = \Config\Services::logger(true);

		if (empty($config))
			$config = new \Config\Encryption();
		$this->config = $config;

		$params = (array) $this->config;

		if ( ! isset($this->key) && self::strlen($key = $this->config->key) > 0)
			$this->key = $key;

		$this->logger->info('Encryption handler Initialized');
	}

// --------------------------------------------------------------------

	/**
	 * Get params, for testing
	 *
	 * @param	array	$params	Input parameters
	 * @return	mixed	associative array of parameters if ok, else false
	 */
	public function getParams($params)
	{
		//FIXME
		// if no parameters were provided, but we have viable settings already,
		// tell them what we have
		if (empty($params))
		{
			return isset($this->cipher, $this->key) ? [
				'driver'		 => $this->driver,
				'cipher'		 => $this->cipher,
				'key'			 => null,
				'digest'	 => 'sha512',
				'hmac'		 => true
					] : false;
		}

		// if the HMAC parameter is false, zap the HMAC digest & key
		if (isset($params['hmac']) && $params['hmac'] === false)
		{
			$params['hmacDigest'] = $params['hmacKey'] = null;
		}
		else
		{
			// make sure we have an HMAC key. WHY?
			if ( ! isset($params['hmacKey']))
			{
				return false;
			}
			// make sure that the digest is supported
			elseif (isset($params['hmacDigest']))
			{
				$params['hmacDigest'] = strtolower($params['hmacDigest']);
				if ( ! isset($this->digests[$params['hmacDigest']]))
				{
					return false;
				}
			}
			else
			{
				// or else set the default digest
				$params['hmacDigest'] = 'sha512';
			}
		}

		// build the complete set of parameters we ended up with
		$params = [
			'driver'		 => null,
			'cipher'		 => $params['cipher'],
			'key'			 => $params['key'],
			'hmacDigest'	 => $params['hmacDigest'],
			'hmacKey'		 => $params['hmacKey']
		];

		return $params;
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

	/**
	 * __get() magic, providing readonly access to some of our properties
	 *
	 * @param	string	$key	Property name
	 * @return	mixed
	 */
	public function __get($key)
	{
		//FIXME
		// Because aliases
		if (($key === 'mode') && isset($this->modes[$this->handler]))
		{
			return array_search($this->mode, $this->modes[$this->handler], true);
		}

		if (in_array($key, ['cipher', 'key', 'handler', 'handlers', 'digests'], true))
		{
			return $this->{$key};
		}

		return null;
	}

}
