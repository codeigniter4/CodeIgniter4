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

	/**
	 * List of available modes
	 *
	 * @var	array
	 */
	protected $modes = [
		'cbc'	 => 'cbc',
		'ecb'	 => 'ecb',
		'ofb'	 => 'ofb',
		'cfb'	 => 'cfb',
		'cfb8'	 => 'cfb8',
		'ctr'	 => 'ctr',
		'stream' => '',
		'xts'	 => 'xts'
	];

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
			$this->cipherAlias($params['cipher']);
			$this->cipher = $params['cipher'];
		}

		if ( ! empty($params['mode']))
		{
			$params['mode'] = strtolower($params['mode']);
			if ( ! isset($this->modes[$params['mode']]))
			{
				$this->logger->error('Encryption: OpenSSL mode ' . strtoupper($params['mode']) . ' is not available.');
			}
			else
			{
				$this->mode = $this->modes[$params['mode']];
			}
		}

		if ( ! empty($params['key']))
		{
			$this->key = $params['key'];
		}

		if (isset($this->cipher, $this->mode))
		{
			// This is mostly for the stream mode, which doesn't get suffixed in OpenSSL
			$handle = empty($this->mode) ? $this->cipher : $this->cipher . '-' . $this->mode;

			if ( ! in_array($handle, openssl_get_cipher_methods(), true))
			{
				$this->handle = null;
				$this->logger->error('Encryption: Unable to initialize OpenSSL with method ' . strtoupper($handle) . '.');
			}
			else
			{
				$this->handle = $handle;
				$this->logger->info('Encryption: OpenSSL initialized with method ' . strtoupper($handle) . '.');
			}
		}
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
		if (empty($params['cipher']))
		{
			return false;
		}

		$iv = ($iv_size = openssl_cipher_iv_length($params['cipher'])) ? $this->createKey($iv_size) : null;

		$data = openssl_encrypt(
				$data, $params['cipher'], $params['key'], OPENSSL_RAW_DATA, $iv
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
	 * @param	array	$params	Input parameters
	 * @return	string
	 */
	public function decrypt($data, array $params = null)
	{

		if ($iv_size = openssl_cipher_iv_length($params['cipher']))
		{
			$iv = self::substr($data, 0, $iv_size);
			$data = self::substr($data, $iv_size);
		}
		else
		{
			$iv = null;
		}

		return empty($params['cipher']) ? false : openssl_decrypt(
						$data, $params['cipher'], $params['key'], OPENSSL_RAW_DATA, $iv
		);
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
	protected function cipherAlias(&$cipher)
	{
		static $dictionary;

		if (empty($dictionary))
		{
			$dictionary = [
				'rijndael-128'	 => 'aes-128',
				'rijndael-256'	 => 'aes-256',
				'tripledes'		 => 'des-ede3',
				'blowfish'		 => 'bf',
				'cast-128'		 => 'cast5',
				'arcfour'		 => 'rc4-40',
				'rc4'			 => 'rc4-40'
			];
		}

		if (isset($dictionary[$cipher]))
		{
			$cipher = $dictionary[$cipher];
		}
	}

}
