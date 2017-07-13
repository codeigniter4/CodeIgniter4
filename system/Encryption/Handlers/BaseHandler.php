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
	protected $cipher = 'aes-256';

	/**
	 * Cipher mode
	 *
	 * @var	string
	 */
	protected $mode = 'cbc';

	/**
	 * Cipher handle
	 *
	 * @var	mixed
	 */
	protected $handle;

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
	 * List of available modes. Override by driver
	 *
	 * @var	array
	 */
	protected $modes = [];

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
		// if no parameters were provided, but we have viable settings already,
		// tell them what we have
		if (empty($params))
		{
			return isset($this->cipher, $this->mode, $this->key) ? [
				'handle'		 => $this->handle,
				'cipher'		 => $this->cipher,
				'mode'			 => $this->mode,
				'key'			 => null,
				'base64'		 => true,
				'hmac_digest'	 => 'sha512',
				'hmac_key'		 => null
					] : false;
		}

		// if a cipher mode was given, make sure it is valid
		if (isset($params['mode']))
		{
			$params['mode'] = strtolower($params['mode']);
			if ( ! isset($this->modes[$params['mode']]))
			{
				return false;
			}
			else
			{
				$params['mode'] = $this->modes[$params['mode']];
			}
		}

		// if the HMAC parameter is false, zap the HMAC digest & key
		if (isset($params['hmac']) && $params['hmac'] === false)
		{
			$params['hmac_digest'] = $params['hmac_key'] = null;
		}
		else
		{
			// make sure we have an HMAC key. WHY?
			if ( ! isset($params['hmac_key']))
			{
				return false;
			}
			// make sure that the digest is supported
			elseif (isset($params['hmac_digest']))
			{
				$params['hmac_digest'] = strtolower($params['hmac_digest']);
				if ( ! isset($this->digests[$params['hmac_digest']]))
				{
					return false;
				}
			}
			else
			{
				// or else set the default digest
				$params['hmac_digest'] = 'sha512';
			}
		}

		// build the complete set of parameters we ended up with
		$params = [
			'handle'		 => null,
			'cipher'		 => $params['cipher'],
			'mode'			 => $params['mode'],
			'key'			 => $params['key'],
			'base64'		 => isset($params['raw_data']) ?  ! $params['raw_data'] : false,
			'hmac_digest'	 => $params['hmac_digest'],
			'hmac_key'		 => $params['hmac_key']
		];

		// and adjust the handle appropriately
		$this->cipherAlias($params['cipher']);
		$params['handle'] = ($params['cipher'] !== $this->cipher OR $params['mode'] !== $this->mode) ? $this->getHandle($params['cipher'], $params['mode']) : $this->handle;

		return $params;
	}

// --------------------------------------------------------------------

	/**
	 * HKDF
	 *
	 * @link	https://tools.ietf.org/rfc/rfc5869.txt
	 * @param	$key	Input key
	 * @param	$digest	A SHA-2 hashing algorithm
	 * @param	$salt	Optional salt
	 * @param	$length	Output length (defaults to the selected digest size)
	 * @param	$info	Optional context/application-specific info
	 * @return	string	A pseudo-random key
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
		for ($key_block = '', $block_index = 1; self::strlen($key) < $length; $block_index ++ )
		{
			$key_block = hash_hmac($digest, $key_block . $info . chr($block_index), $prk, true);
			$key .= $key_block;
		}

		return self::substr($key, 0, $length);
	}

// --------------------------------------------------------------------

	/**
	 * Cipher alias
	 *
	 * Tries to translate cipher names as appropriate for this handler
	 *
	 * @param	string	$cipher	Cipher name
	 * @return	void
	 */
	abstract protected function cipherAlias(&$cipher);

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
		// Because aliases
		if (($key === 'mode') && isset($this->modes[$this->handler]))
		{
			return array_search($this->mode, $this->modes[$this->handler], true);
		}

		if (in_array($key, ['cipher', 'mode', 'key', 'handler', 'handlers', 'digests'], true))
		{
			return $this->{$key};
		}

		return null;
	}

}
