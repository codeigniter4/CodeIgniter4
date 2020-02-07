<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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

namespace CodeIgniter\Encryption\Handlers;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Encryption\Exceptions\EncryptionException;

/**
 * Encryption handling for OpenSSL library
 */
class OpenSSLHandler extends BaseHandler
{

	/**
	 * HMAC digest to use
	 */
	protected $digest = 'SHA512';

	/**
	 * Cipher to use
	 */
	protected $cipher = 'AES-256-CTR';

	// --------------------------------------------------------------------

	/**
	 * Initialize OpenSSL, remembering parameters
	 *
	 * @param BaseConfig $config
	 *
	 * @throws \CodeIgniter\Encryption\EncryptionException
	 */
	public function __construct(BaseConfig $config = null)
	{
		parent::__construct($config);
	}

	/**
	 * Encrypt plaintext, with optional HMAC and base64 encoding
	 *
	 * @param  string $data   Input data
	 * @param  array  $params Over-ridden parameters, specifically the key
	 * @return string
	 * @throws \CodeIgniter\Encryption\EncryptionException
	 */
	public function encrypt($data, $params = null)
	{
		// Allow key over-ride
		if (! empty($params))
		{
			if (isset($params['key']))
			{
				$this->key = $params['key'];
			}
			else
			{
				$this->key = $params;
			}
		}
		if (empty($this->key))
		{
			throw EncryptionException::forNeedsStarterKey();
		}

		// derive a secret key
		$secret = \hash_hkdf($this->digest, $this->key);

		// basic encryption
		$iv = ($iv_size = \openssl_cipher_iv_length($this->cipher)) ? \openssl_random_pseudo_bytes($iv_size) : null;

		$data = \openssl_encrypt($data, $this->cipher, $secret, OPENSSL_RAW_DATA, $iv);

		if ($data === false)
		{
			throw EncryptionException::forEncryptionFailed();
		}

		$result = $iv . $data;

		$hmacKey = \hash_hmac($this->digest, $result, $secret, true);
		$result  = $hmacKey . $result;

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Decrypt ciphertext, with optional HMAC and base64 encoding
	 *
	 * @param  string $data   Encrypted data
	 * @param  array  $params Over-ridden parameters, specifically the key
	 * @return string
	 * @throws \CodeIgniter\Encryption\EncryptionException
	 */
	public function decrypt($data, $params = null)
	{
		// Allow key over-ride
		if (! empty($params))
		{
			if (isset($params['key']))
			{
				$this->key = $params['key'];
			}
			else
			{
				$this->key = $params;
			}
		}
		if (empty($this->key))
		{
			throw EncryptionException::forStarterKeyNeeded();
		}

		// derive a secret key
		$secret = \hash_hkdf($this->digest, $this->key);

		$hmacLength = self::substr($this->digest, 3) / 8;
		$hmacKey    = self::substr($data, 0, $hmacLength);
		$data       = self::substr($data, $hmacLength);
		$hmacCalc   = \hash_hmac($this->digest, $data, $secret, true);
		if (! hash_equals($hmacKey, $hmacCalc))
		{
			throw EncryptionException::forAuthenticationFailed();
		}

		if ($iv_size = \openssl_cipher_iv_length($this->cipher))
		{
			$iv   = self::substr($data, 0, $iv_size);
			$data = self::substr($data, $iv_size);
		}
		else
		{
			$iv = null;
		}

		return \openssl_decrypt($data, $this->cipher, $secret, OPENSSL_RAW_DATA, $iv);
	}

}
