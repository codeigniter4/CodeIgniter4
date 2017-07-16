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
class OpenSSLHandler extends BaseHandler
{
	// --------------------------------------------------------------------

	/**
	 * Initialize OpenSSL, remembering parameters
	 *
	 * @param	array	$params	Configuration parameters
	 * @return	void
	 */
	public function __construct($params = [])
	{
		parent::__construct($params);

		if (empty($this->cipher))
			throw new \CodeIgniter\Encryption\EncryptionException("OpenSSL handler configuration missing cipher.");
		if ( ! in_array($this->cipher, openssl_get_cipher_methods(), true))
			throw new \CodeIgniter\Encryption\EncryptionException("OpenSSL handler does not support the " . $this->cipher . " cipher.");

		if (empty($this->key))
			throw new \CodeIgniter\Encryption\EncryptionException("OpenSSL handler configuration missing key.");
		if (empty($this->hmac))
			throw new \CodeIgniter\Encryption\EncryptionException("OpenSSL handler configuration missing HMAC control.");
		if (empty($this->digest))
			throw new \CodeIgniter\Encryption\EncryptionException("OpenSSL handler configuration missing HMAC digest.");
		if (empty($this->base64))
			throw new \CodeIgniter\Encryption\EncryptionException("OpenSSL handler configuration missing base64 control.");

		$this->logger->info('OpenSSL handler initialized with cipher ' . $this->cipher . '.');
	}

	/**
	 * Encrypt plaintext, with optional HMAC and base64 encoding
	 *
	 * @param	string	$data	Input data
	 * @return	string
	 */
	public function encrypt($data)
	{
		// basic encryption	
		$iv = ($iv_size = openssl_cipher_iv_length($this->cipher)) ? openssl_random_pseudo_bytes($iv_size) : null;

		$data = openssl_encrypt($data, $this->cipher, $this->secret, OPENSSL_RAW_DATA, $iv);

		if ($data === false)
			return false;

		$result = $iv . $data;

		// HMAC?
		if ($this->hmac == 'hmac')
		{
			$hmacKey = hash_hmac($this->digest, $result, $this->secret,true);
			$result = $hmacKey . $result;
		}

		if ($this->base64 == 'base64')
			$result = base64_encode($result);

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Decrypt ciphertext, with optional HMAC and base64 encoding
	 *
	 * @param	string	$data	Encrypted data
	 * @return	string
	 */
	public function decrypt($data)
	{
		if ($this->base64 == 'base64')
			$data = base64_decode($data);

		// HMAC?
		if ($this->hmac == 'hmac')
		{
			$hmacLength = self::substr($this->digest,3) / 8;
			$hmacKey = self::substr($data,0,$hmacLength);
			$data = self::substr($data,$hmacLength);
			$hmacCalc = hash_hmac($this->digest, $data, $this->secret,true);
			if ($hmacKey != $hmacCalc)
				throw new \CodeIgniter\Encryption\EncryptionException("Message authentication failed.");
		}
		
		if ($iv_size = openssl_cipher_iv_length($this->cipher))
		{
			$iv = self::substr($data, 0, $iv_size);
			$data = self::substr($data, $iv_size);
		}
		else
		{
			$iv = null;
		}

		return openssl_decrypt($data, $this->cipher, $this->secret, OPENSSL_RAW_DATA, $iv);
	}

}
