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

/**
 * Encryption handling for OpenSSL library
 *
 * @see \CodeIgniter\Encryption\Handlers\OpenSSLHandlerTest
 */
class OpenSSLHandler extends BaseHandler
{
    /**
     * HMAC digest to use
     *
     * @var string
     */
    protected $digest = 'SHA512';

    /**
     * List of supported HMAC algorithms
     *
     * @var array [name => digest size]
     */
    protected array $digestSize = [
        'SHA224' => 28,
        'SHA256' => 32,
        'SHA384' => 48,
        'SHA512' => 64,
    ];

    /**
     * Cipher to use
     *
     * @var string
     */
    protected $cipher = 'AES-256-CTR';

    /**
     * Starter key
     *
     * @var string
     */
    protected $key = '';

    /**
     * Whether to fall back to previous keys when decryption fails.
     */
    protected bool $previousKeysFallbackEnabled = false;

    /**
     * List of previous keys for fallback decryption.
     *
     * @var list<string>
     */
    protected array $previousKeys = [];

    /**
     * Whether the cipher-text should be raw. If set to false, then it will be base64 encoded.
     */
    protected bool $rawData = true;

    /**
     * Encryption key info.
     * This setting is only used by OpenSSLHandler.
     *
     * Set to 'encryption' for CI3 Encryption compatibility.
     */
    public string $encryptKeyInfo = '';

    /**
     * Authentication key info.
     * This setting is only used by OpenSSLHandler.
     *
     * Set to 'authentication' for CI3 Encryption compatibility.
     */
    public string $authKeyInfo = '';

    /**
     * {@inheritDoc}
     */
    public function encrypt(#[SensitiveParameter] $data, #[SensitiveParameter] $params = null)
    {
        // Allow key override
        if ($params !== null) {
            $this->key = is_array($params) && isset($params['key']) ? $params['key'] : $params;
        }

        if (empty($this->key)) {
            throw EncryptionException::forNeedsStarterKey();
        }

        // derive a secret key
        $encryptKey = \hash_hkdf($this->digest, $this->key, 0, $this->encryptKeyInfo);

        // basic encryption
        $iv = ($ivSize = \openssl_cipher_iv_length($this->cipher)) ? \openssl_random_pseudo_bytes($ivSize) : null;

        $data = \openssl_encrypt($data, $this->cipher, $encryptKey, OPENSSL_RAW_DATA, $iv);

        if ($data === false) {
            throw EncryptionException::forEncryptionFailed();
        }

        $result = $this->rawData ? $iv . $data : base64_encode($iv . $data);

        // derive a secret key
        $authKey = \hash_hkdf($this->digest, $this->key, 0, $this->authKeyInfo);

        $hmacKey = \hash_hmac($this->digest, $result, $authKey, $this->rawData);

        return $hmacKey . $result;
    }

    /**
     * {@inheritDoc}
     */
    public function decrypt($data, #[SensitiveParameter] $params = null)
    {
        // Allow key override
        if ($params !== null) {
            $this->key = is_array($params) && isset($params['key']) ? $params['key'] : $params;
        }

        if (empty($this->key)) {
            throw EncryptionException::forNeedsStarterKey();
        }

        try {
            $result = $this->decryptWithKey($data, $this->key);
        } catch (EncryptionException $e) {
            $result    = false;
            $exception = $e;
        }

        if ($result === false && $this->previousKeysFallbackEnabled && ! empty($this->previousKeys)) {
            foreach ($this->previousKeys as $previousKey) {
                try {
                    $result = $this->decryptWithKey($data, $previousKey);
                    if ($result !== false) {
                        return $result;
                    }
                } catch (EncryptionException) {
                    // Try next key
                }
            }
        }

        if (isset($exception)) {
            throw $exception;
        }

        return $result;
    }

    /**
     * Decrypt the data with the provided key
     *
     * @param string $data
     * @param string $key
     *
     * @return false|string
     *
     * @throws EncryptionException
     */
    protected function decryptWithKey($data, #[SensitiveParameter] $key)
    {
        // derive a secret key
        $authKey = \hash_hkdf($this->digest, $key, 0, $this->authKeyInfo);

        $hmacLength = $this->rawData
            ? $this->digestSize[$this->digest]
            : $this->digestSize[$this->digest] * 2;

        $hmacKey  = self::substr($data, 0, $hmacLength);
        $data     = self::substr($data, $hmacLength);
        $hmacCalc = \hash_hmac($this->digest, $data, $authKey, $this->rawData);

        if (! hash_equals($hmacKey, $hmacCalc)) {
            throw EncryptionException::forAuthenticationFailed();
        }

        $data = $this->rawData ? $data : base64_decode($data, true);

        if ($ivSize = \openssl_cipher_iv_length($this->cipher)) {
            $iv   = self::substr($data, 0, $ivSize);
            $data = self::substr($data, $ivSize);
        } else {
            $iv = null;
        }

        // derive a secret key
        $encryptKey = \hash_hkdf($this->digest, $key, 0, $this->encryptKeyInfo);

        return \openssl_decrypt($data, $this->cipher, $encryptKey, OPENSSL_RAW_DATA, $iv);
    }
}
