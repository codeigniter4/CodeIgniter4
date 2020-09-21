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

use CodeIgniter\Encryption\Exceptions\EncryptionException;

/**
 * SodiumHandler uses libsodium in encryption.
 *
 * @see https://github.com/jedisct1/libsodium/issues/392
 */
class SodiumHandler extends BaseHandler
{
	/**
	 * Starter key
	 *
	 * @var string
	 */
	protected $key = '';

	/**
	 * Block size for padding message.
	 *
	 * @var integer
	 */
	protected $blockSize = 512;

	/**
	 * {@inheritDoc}
	 */
	public function encrypt($data, $params = null)
	{
		$this->parseParams($params);

		if (empty($this->key))
		{
			throw EncryptionException::forNeedsStarterKey();
		}

		// create a nonce for this operation
		$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES); // 24 bytes

		// pad to a maximum of 512 bytes chunks
		$paddedLength  = $this->blockSize <= 512 ? $this->blockSize : 512;
		$paddedMessage = sodium_pad($data, $paddedLength);

		// encrypt message and combine with nonce
		$ciphertext = $nonce . sodium_crypto_secretbox($paddedMessage, $nonce, $this->key);

		// cleanup buffers
		sodium_memzero($data);
		sodium_memzero($this->key);

		return $ciphertext;
	}

	/**
	 * {@inheritDoc}
	 */
	public function decrypt($data, $params = null)
	{
		$this->parseParams($params);

		if (empty($this->key))
		{
			throw EncryptionException::forNeedsStarterKey();
		}

		if (mb_strlen($data, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES))
		{
			// message was truncated
			throw EncryptionException::forAuthenticationFailed();
		}

		// Extract info from encrypted data
		$nonce      = self::substr($data, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$ciphertext = self::substr($data, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null);

		// decrypt and account for extra padding
		$paddedLength    = $this->blockSize <= 512 ? $this->blockSize : 512;
		$paddedPlaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->key);

		if ($paddedPlaintext === false)
		{
			// message was tampered in transit
			throw EncryptionException::forAuthenticationFailed(); // @codeCoverageIgnore
		}

		$plaintext = sodium_unpad($paddedPlaintext, $paddedLength);

		// cleanup buffers
		sodium_memzero($ciphertext);
		sodium_memzero($this->key);

		return $plaintext;
	}

	/**
	 * Parse the $params before doing assignment.
	 *
	 * @param array|string|null $params
	 *
	 * @throws \CodeIgniter\Encryption\Exceptions\EncryptionException If key is empty
	 *
	 * @return void
	 */
	protected function parseParams($params)
	{
		if ($params === null)
		{
			return;
		}

		if (is_array($params))
		{
			if (isset($params['key']))
			{
				$this->key = $params['key'];
			}

			if (isset($params['block_size']))
			{
				$this->blockSize = $params['block_size'];
			}

			return;
		}

		$this->key = (string) $params;
	}
}
