<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Encryption\Handlers;

use CodeIgniter\Encryption\Exceptions\EncryptionException;
use SensitiveParameter;

/**
 * SodiumHandler uses libsodium in encryption.
 *
 * @see https://github.com/jedisct1/libsodium/issues/392
 * @see \CodeIgniter\Encryption\Handlers\SodiumHandlerTest
 */
class SodiumHandler extends BaseHandler
{
    /**
     * Starter key
     *
     * @var string|null Null is used for buffer cleanup.
     */
    protected $key = '';

    /**
     * Block size for padding message.
     *
     * @var int
     */
    protected $blockSize = 16;

    /**
     * {@inheritDoc}
     */
    public function encrypt(#[SensitiveParameter] $data, #[SensitiveParameter] $params = null)
    {
        // Allow key override
        $key = $params !== null
            ? (is_array($params) && isset($params['key']) ? $params['key'] : $params)
            : $this->key;

        // Allow blockSize override
        $blockSize = (is_array($params) && isset($params['blockSize']))
            ? $params['blockSize']
            : $this->blockSize;

        if (empty($key)) {
            throw EncryptionException::forNeedsStarterKey();
        }

        // create a nonce for this operation
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES); // 24 bytes

        // add padding before we encrypt the data
        if ($blockSize <= 0) {
            throw EncryptionException::forEncryptionFailed();
        }

        $data = sodium_pad($data, $blockSize);

        // encrypt message and combine with nonce
        $ciphertext = $nonce . sodium_crypto_secretbox($data, $nonce, $key);

        // cleanup buffers
        sodium_memzero($data);
        sodium_memzero($key);

        return $ciphertext;
    }

    /**
     * {@inheritDoc}
     */
    public function decrypt($data, #[SensitiveParameter] $params = null)
    {
        // Allow key override
        $key = $params !== null
            ? (is_array($params) && isset($params['key']) ? $params['key'] : $params)
            : $this->key;

        // Allow blockSize override
        $blockSize = (is_array($params) && isset($params['blockSize']))
            ? $params['blockSize']
            : $this->blockSize;

        if (empty($key)) {
            throw EncryptionException::forNeedsStarterKey();
        }

        if (mb_strlen($data, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
            // message was truncated
            throw EncryptionException::forAuthenticationFailed();
        }

        // Extract info from encrypted data
        $nonce      = self::substr($data, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = self::substr($data, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        // decrypt data
        $data = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

        if ($data === false) {
            // message was tampered in transit
            throw EncryptionException::forAuthenticationFailed(); // @codeCoverageIgnore
        }

        // remove extra padding during encryption
        if ($blockSize <= 0) {
            throw EncryptionException::forAuthenticationFailed();
        }

        $data = sodium_unpad($data, $blockSize);

        // cleanup buffers
        sodium_memzero($ciphertext);
        sodium_memzero($key);

        return $data;
    }

    /**
     * Parse the $params before doing assignment.
     *
     * @param array|string|null $params
     *
     * @return void
     *
     * @throws EncryptionException If key is empty
     *
     * @deprecated 4.7.0 No longer used.
     */
    protected function parseParams($params)
    {
        if ($params === null) {
            return;
        }

        if (is_array($params)) {
            if (isset($params['key'])) {
                $this->key = $params['key'];
            }

            if (isset($params['blockSize'])) {
                $this->blockSize = $params['blockSize'];
            }

            return;
        }

        $this->key = (string) $params;
    }
}
