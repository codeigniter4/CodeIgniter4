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
class SodiumHandler extends BaseHandler
{
	// --------------------------------------------------------------------

	/**
	 * Initialize Sodium.
	 * 
	 * Cipher is ignored, and only GCM mode is available.
	 *
	 * @param	array	$params	Configuration parameters
	 * @return	void
	 */
	public function __construct($params = [])
	{
		parent::__construct($params);

		if (function_exists('sodium_init') && sodium_init())
		{
			$this->logger->info('Encryption: Sodium initialized.');
		}
		else
		{
			$this->logger->error('Encryption: Unable to initialize Sodium.');
		}
	}

	/**
	 * Encrypt plaintext, with optional HMAC and base64 encoding
	 *
	 * @param	string	$data	Input data
	 * @param	array	$params	Over-ridden parameters, specifically the key
	 * @return	string
	 * @throws \CodeIgniter\Encryption\EncryptionException
	 */
	public function encrypt($data, $params = null)
	{
		// Allow key over-ride
		if ( ! empty($params))
			if (isset($params['key']))
				$this->key = $params['key'];
			else
				$this->key = $params;
		if (empty($this->key))
			throw new \CodeIgniter\Encryption\EncryptionException("Sodium handler configuration missing key.");

		// derive a secret key			
		$secret = strcmp(phpversion(), '7.1.2') >= 0 ?
				\hash_hkdf($this->digest, $this->key) :
				\CodeIgniter\Encryption\Encryption::hkdf($this->key, $this->digest);

		$nonce = \Sodium\randombytes_buf(\Sodium\CRYPTO_SECRETBOX_NONCEBYTES);

		$ciphertext = \Sodium\crypto_secretbox($data, $nonce, $secret);

		if ($ciphertext === false)
		{
			return false;
		}

		$result = $nonce . $ciphertext;

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Decrypt ciphertext, with optional HMAC and base64 encoding
	 *
	 * @param	string	$data	Encrypted data
	 * @param	array	$params	Over-ridden parameters, specifically the key
	 * @return	string
	 * @throws \CodeIgniter\Encryption\EncryptionException
	 */
	public function decrypt($data, $params = null)
	{
		// Allow key over-ride
		if ( ! empty($params))
			if (isset($params['key']))
				$this->key = $params['key'];
			else
				$this->key = $params;
		if (empty($this->key))
			throw new \CodeIgniter\Encryption\EncryptionException("Sodium handler configuration missing key.");

		// derive a secret key			
		$secret = strcmp(phpversion(), '7.1.2') >= 0 ?
				\hash_hkdf($this->digest, $this->key) :
				\CodeIgniter\Encryption\Encryption::hkdf($this->key, $this->digest);

		// split the data into nonce & ciphertext
		$nonce = self::substr($data, 0, \Sodium\CRYPTO_SECRETBOX_NONCEBYTES);
		$data = self::substr($data, \Sodium\CRYPTO_SECRETBOX_NONCEBYTES);

		$plaintext = \Sodium\crypto_secretbox_open($data, $nonce, $secret);

		if ($plaintext === false)
		{
			throw new EncryptionException("Bad ciphertext");
		}

		return $plaintext;
	}

}
