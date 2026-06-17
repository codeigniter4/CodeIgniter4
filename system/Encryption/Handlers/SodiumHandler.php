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
        $key       = $this->key;
        $blockSize = $this->blockSize;

        // Nadpisywanie parametrów
        if ($params !== null) {
            if (is_array($params)) {
                $key       = array_key_exists('key', $params) ? $params['key'] : $key;
                $blockSize = array_key_exists('blockSize', $params) ? $params['blockSize'] : $blockSize;
            } else {
                $key = $params;
            }
        }

        // Walidacja klucza
        if (empty($key) || strlen((string) $key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw EncryptionException::forNeedsStarterKey();
        }

        if ($blockSize <= 0) {
            throw EncryptionException::forEncryptionFailed();
        }

        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES); // 24 bytes
        $data  = sodium_pad($data, $blockSize);

        $ciphertext = $nonce . sodium_crypto_secretbox($data, $nonce, $key);

        sodium_memzero($data); // Czyścimy tylko spadowany bufor wiadomości

        return $ciphertext;
    }

    /**
     * {@inheritDoc}
     */
    public function decrypt($data, #[SensitiveParameter] $params = null)
    {
        $key       = $this->key;
        $blockSize = $this->blockSize;

        // Nadpisywanie parametrów
        if ($params !== null) {
            if (is_array($params)) {
                $key       = array_key_exists('key', $params) ? $params['key'] : $key;
                $blockSize = array_key_exists('blockSize', $params) ? $params['blockSize'] : $blockSize;
            } else {
                $key = $params;
            }
        }

        // Walidacja klucza
        if (empty($key) || strlen((string) $key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw EncryptionException::forNeedsStarterKey();
        }

        if (mb_strlen($data, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
            throw EncryptionException::forAuthenticationFailed();
        }

        $nonce      = self::substr($data, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = self::substr($data, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $data = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

        if ($data === false || $blockSize <= 0) {
            throw EncryptionException::forAuthenticationFailed();
        }

        $data = sodium_unpad($data, $blockSize);

        sodium_memzero($ciphertext); // Czyścimy wyodrębniony szyfrogram

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
