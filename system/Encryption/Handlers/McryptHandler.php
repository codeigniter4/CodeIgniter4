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
class McryptHandler extends BaseHandler
{

	/**
	 * List of available modes
	 *
	 * @var	array
	 */
	protected $modes = [
		'cbc' => 'cbc',
		'ecb' => 'ecb',
		'ofb' => 'nofb',
		'ofb8' => 'ofb',
		'cfb' => 'ncfb',
		'cfb8' => 'cfb',
		'ctr' => 'ctr',
		'stream' => 'stream'
	];

	// --------------------------------------------------------------------

	/**
	 * Initialize MCrypt
	 *
	 * @param	array	$params	Configuration parameters
	 * @return	void
	 * 
	 * @throws \CodeIgniter\Encryption\EncryptionException
	 */
	protected function initializeIt($params)
	{
		if ( ! empty($params['cipher']))
		{
			$params['cipher'] = strtolower($params['cipher']);
			$this->cipherAlias($params['cipher']);

			if ( ! in_array($params['cipher'], mcrypt_list_algorithms(), true))
			{
				throw new EncryptionException('MCrypt cipher ' . strtoupper($params['cipher']) . ' is not available.');
			}
			else
			{
				$this->cipher = $params['cipher'];
			}
		}

		if ( ! empty($params['mode']))
		{
			$params['mode'] = strtolower($params['mode']);
			if ( ! isset($this->modes[$params['mode']]))
			{
				throw new EncryptionException('MCrypt mode ' . strtoupper($params['cipher']) . ' is not available.');
			}
			else
			{
				$this->mode = $this->modes[$params['mode']];
			}
		}

		if (isset($this->cipher, $this->mode))
		{
			if (is_resource($this->handle) && (strtolower(mcrypt_enc_get_algorithms_name($this->handle)) !== $this->cipher
					OR strtolower(mcrypt_enc_getmodes_name($this->handle)) !== $this->mode)
			)
			{
				mcrypt_module_close($this->handle);
			}

			if ($this->handle = mcrypt_module_open($this->cipher, '', $this->mode, ''))
			{
				$this->logger->info('Encryption: MCrypt cipher ' . strtoupper($this->cipher) . ' initialized in ' . strtoupper($this->mode) . ' mode.');
			}
			else
			{
				throw new EncryptionException('Unable to initialize MCrypt with cipher ' . strtoupper($this->cipher) . ' in ' . strtoupper($this->mode) . ' mode.');
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
	public function encryptIt($data, array $params = null)
	{

		if ( ! is_resource($params['handle']))
		{
			return false;
		}

		// The greater-than-1 comparison is mostly a work-around for a bug,
		// where 1 is returned for ARCFour instead of 0.
		$iv = (($iv_size = mcrypt_enc_get_iv_size($params['handle'])) > 1) ? $this->createKey($iv_size) : null;

		if (mcrypt_generic_init($params['handle'], $params['key'], $iv) < 0)
		{
			if ($params['handle'] !== $this->handle)
			{
				mcrypt_module_close($params['handle']);
			}

			return false;
		}

		// Use PKCS#7 padding in order to ensure compatibility with OpenSSL
		// and other implementations outside of PHP.
		if (in_array(strtolower(mcrypt_enc_getmodes_name($params['handle'])), ['cbc', 'ecb'], true))
		{
			$block_size = mcrypt_enc_get_block_size($params['handle']);
			$pad = $block_size - (self::strlen($data) % $block_size);
			$data .= str_repeat(chr($pad), $pad);
		}

		// Work-around for yet another strange behavior in MCrypt.
		//
		// When encrypting in ECB mode, the IV is ignored. Yet
		// mcrypt_enc_get_iv_size() returns a value larger than 0
		// even if ECB is used AND mcrypt_generic_init() complains
		// if you don't pass an IV with length equal to the said
		// return value.
		//
		// This probably would've been fine (even though still wasteful),
		// but OpenSSL isn't that dumb and we need to make the process
		// portable, so ...
		$data = (mcrypt_enc_getmodes_name($params['handle']) !== 'ECB') ? $iv . mcrypt_generic($params['handle'], $data) : mcrypt_generic($params['handle'], $data);

		mcrypt_generic_deinit($params['handle']);
		if ($params['handle'] !== $this->handle)
		{
			mcrypt_module_close($params['handle']);
		}

		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Decrypt
	 *
	 * @param	string	$data	Encrypted data
	 * @param	array	$params	Input parameters
	 * @return	string
	 */
	public function decryptIt($data, array $params = null)
	{

		if ( ! is_resource($params['handle']))
		{
			return false;
		}

		// The greater-than-1 comparison is mostly a work-around for a bug,
		// where 1 is returned for ARCFour instead of 0.
		if (($iv_size = mcrypt_enc_get_iv_size($params['handle'])) > 1)
		{
			if (mcrypt_enc_getmodes_name($params['handle']) !== 'ECB')
			{
				$iv = self::substr($data, 0, $iv_size);
				$data = self::substr($data, $iv_size);
			}
			else
			{
				// MCrypt is dumb and this is ignored, only size matters
				$iv = str_repeat("\x0", $iv_size);
			}
		}
		else
		{
			$iv = null;
		}

		if (mcrypt_generic_init($params['handle'], $params['key'], $iv) < 0)
		{
			if ($params['handle'] !== $this->handle)
			{
				mcrypt_module_close($params['handle']);
			}

			return false;
		}

		$data = mdecrypt_generic($params['handle'], $data);
		// Remove PKCS#7 padding, if necessary
		if (in_array(strtolower(mcrypt_enc_getmodes_name($params['handle'])), ['cbc', 'ecb'], true))
		{
			$data = self::substr($data, 0, -ord($data[self::strlen($data) - 1]));
		}

		mcrypt_generic_deinit($params['handle']);
		if ($params['handle'] !== $this->handle)
		{
			mcrypt_module_close($params['handle']);
		}

		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Get MCrypt handle
	 *
	 * @param	string	$cipher	Cipher name
	 * @param	string	$mode	Encryption mode
	 * @return	resource
	 */
	protected function getHandle($cipher, $mode)
	{
		return mcrypt_module_open($cipher, '', $mode, '');
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
				'aes-128' => 'rijndael-128',
				'aes-192' => 'rijndael-128',
				'aes-256' => 'rijndael-128',
				'des3-ede3' => 'tripledes',
				'bf' => 'blowfish',
				'cast5' => 'cast-128',
				'rc4' => 'arcfour',
				'rc4-40' => 'arcfour'
			];
		}

		if (isset($dictionary[$cipher]))
		{
			$cipher = $dictionary[$cipher];
		}
	}

}
