<?php

namespace CodeIgniter\Encryption\Handlers;

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
	protected $cipher = 'aes-128';

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
	 * Encryption key
	 *
	 * @var	string
	 */
	protected $key;

	/**
	 * List of available modes
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
	public function __construct($config)
	{
		if (empty($config))
			$config = new \Config\Encryption();
		$this->config = $config;

		$params = (array) $this->config;

		$this->handlers = [
			'mcrypt' => defined('MCRYPT_DEV_URANDOM'),
			'openssl' => extension_loaded('openssl')
		];

		if ( ! $this->handlers['mcrypt'] && ! $this->handlers['openssl'])
		{
			throw new EncryptionException('Unable to find an available encryption handler.');
		}

		$this->initialize($params);

		if ( ! isset($this->key) && self::strlen($key = $this->config->key) > 0)
		{
			$this->key = $key;
		}

		log_message('info', 'Encryption handler Initialized');
	}

	/**
	 * Initialize
	 *
	 * @param	array	$params	Configuration parameters
	 * @return	CI_Encryption
	 * 
	 * @throws \CodeIgniter\Encryption\EncryptionException
	 */
	public function initialize(array $params)
	{
		if ( ! empty($params['handler']))
		{
			if (isset($this->handlers[$params['handler']]))
			{
				if ($this->handlers[$params['handler']])
				{
					$this->handler = $params['handler'];
				}
				else
				{
					throw new EncryptionException("Driver '" . $params['handler'] . "' is not available.");
				}
			}
			else
			{
				throw new EncryptionException("Unknown handler '" . $params['handler'] . "' cannot be configured.");
			}
		}

		if (empty($this->handler))
		{
			$this->handler = ($this->handlers['openssl'] === true) ? 'openssl' : 'mcrypt';

			log_message('debug', "Encryption: Auto-configured handler '" . $this->handler . "'.");
		}

		empty($params['cipher']) && $params['cipher'] = $this->cipher;
		empty($params['key']) OR $this->key = $params['key'];
		$this->initializeIt($params);
		return $this;
	}

// --------------------------------------------------------------------

	/**
	 * Create a random key
	 *
	 * @param	int	$length	Output length
	 * @return	string
	 */
	public function createKey($length)
	{
		try
		{
			return random_bytes((int) $length);
		} catch (Exception $e)
		{
			throw new EncryptionException('Key creation error: ' . $e->getMessage());
		}

		$is_secure = null;
		$key = openssl_random_pseudo_bytes($length, $is_secure);
		return ($is_secure === true) ? $key : false;
	}

	/**
	 * Encrypt
	 *
	 * @param	string	$data	Input data
	 * @param	array	$params	Input parameters
	 * @return	string
	 */
	public function encrypt($data, array $params = null)
	{
		if (($params = $this->getParams($params)) === false)
		{
			return false;
		}

		isset($params['key']) OR $params['key'] = $this->hkdf($this->key, 'sha512', null, self::strlen($this->key), 'encryption');

		if (($data = $this->encryptIt($data, $params)) === false)
		{
			return false;
		}

		$params['base64'] && $data = base64_encode($data);

		if (isset($params['hmac_digest']))
		{
			isset($params['hmackey']) OR $params['hmackey'] = $this->hkdf($this->key, 'sha512', null, null, 'authentication');
			return hash_hmac($params['hmac_digest'], $data, $params['hmackey'],  ! $params['base64']) . $data;
		}

		return $data;
	}

	/**
	 * Decrypt
	 *
	 * @param	string	$data	Encrypted data
	 * @param	array	$params	Input parameters
	 * @return	string
	 */
	public function decrypt($data, array $params = null)
	{
		if (($params = $this->getParams($params)) === false)
		{
			return false;
		}

		if (isset($params['hmac_digest']))
		{
			// This might look illogical, but it is done during encryption as well ...
			// The 'base64' value is effectively an inverted "raw data" parameter
			$digest_size = ($params['base64']) ? $this->digests[$params['hmac_digest']] * 2 : $this->digests[$params['hmac_digest']];

			if (self::strlen($data) <= $digest_size)
			{
				return false;
			}

			$hmac_input = self::substr($data, 0, $digest_size);
			$data = self::substr($data, $digest_size);

			isset($params['hmackey']) OR $params['hmackey'] = $this->hkdf($this->key, 'sha512', null, null, 'authentication');
			$hmac_check = hash_hmac($params['hmac_digest'], $data, $params['hmackey'],  ! $params['base64']);

			// Time-attack-safe comparison
			$diff = 0;
			for ($i = 0; $i < $digest_size; $i ++ )
			{
				$diff |= ord($hmac_input[$i]) ^ ord($hmac_check[$i]);
			}

			if ($diff !== 0)
			{
				return false;
			}
		}

		if ($params['base64'])
		{
			$data = base64_decode($data);
		}

		isset($params['key']) OR $params['key'] = $this->hkdf($this->key, 'sha512', null, self::strlen($this->key), 'encryption');

		return $this->decryptIt($data, $params);
	}

// --------------------------------------------------------------------

	/**
	 * Get params
	 *
	 * @param	array	$params	Input parameters
	 * @return	array
	 */
	protected function getParams($params)
	{
		if (empty($params))
		{
			return isset($this->cipher, $this->mode, $this->key, $this->handle) ? [
				'handle' => $this->handle,
				'cipher' => $this->cipher,
				'mode' => $this->mode,
				'key' => null,
				'base64' => true,
				'hmac_digest' => 'sha512',
				'hmackey' => null
					] : false;
		}
		elseif ( ! isset($params['cipher'], $params['mode'], $params['key']))
		{
			return false;
		}

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

		if (isset($params['hmac']) && $params['hmac'] === false)
		{
			$params['hmac_digest'] = $params['hmackey'] = null;
		}
		else
		{
			if ( ! isset($params['hmackey']))
			{
				return false;
			}
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
				$params['hmac_digest'] = 'sha512';
			}
		}

		$params = [
			'handle' => null,
			'cipher' => $params['cipher'],
			'mode' => $params['mode'],
			'key' => $params['key'],
			'base64' => isset($params['raw_data']) ?  ! $params['raw_data'] : false,
			'hmac_digest' => $params['hmac_digest'],
			'hmackey' => $params['hmackey']
		];

		$this->cipherAlias($params['cipher']);
		$params['handle'] = ($params['cipher'] !== $this->cipher OR $params['mode'] !== $this->mode) ? $this->getHandle($params['cipher'], $params['mode']) : $this->handle;

		return $params;
	}

// --------------------------------------------------------------------

	/**
	 * Get handler's handle
	 *
	 * @param	string	$cipher	Cipher name
	 * @param	string	$mode	Encryption mode
	 * @return	string
	 */
	abstract protected function getHandle($cipher, $mode);


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
}
