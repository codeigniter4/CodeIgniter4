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
	public function __construct($params = null)
	{
		parent::__construct();

		if ( ! empty($params['cipher']))
		{
			$params['cipher'] = strtolower($params['cipher']);
			$this->cipher = $params['cipher'];
		}

		if ( ! empty($params['key']))
		{
			$this->key = $params['key'];
		}

		if (isset($this->cipher))
		{
			if ( ! in_array($this->cipher, openssl_get_cipher_methods(), true))
			{
				$this->logger->error('Encryption: Unable to initialize OpenSSL with cipher ' . $this->cipher . '.');
				$this->cipher = null;
			}
			else
			{
				$this->logger->info('Encryption: OpenSSL initialized with cipher ' . $this->cipher . '.');
			}
		}

		$this->secret = hash_hkdf($this->cipher, $this->key);
	}

	/**
	 * Encrypt
	 *
	 * @param	string	$data	Input data
	 * @return	string
	 */
	public function encrypt($data)
	{
		$iv = ($iv_size = openssl_cipher_iv_length($this->cipher)) ? $this->createKey($iv_size) : null;

		$data = openssl_encrypt(
				$data, $this->cipher, $this->secret, OPENSSL_RAW_DATA, $iv
		);

		if ($data === false)
		{
			return false;
		}

		return $iv . $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Decrypt
	 *
	 * @param	string	$data	Encrypted data
	 * @return	string
	 */
	public function decrypt($data)
	{

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
