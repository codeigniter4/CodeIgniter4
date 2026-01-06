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

namespace CodeIgniter\Encryption;

use CodeIgniter\Encryption\Exceptions\EncryptionException;
use SensitiveParameter;

/**
 * Key Rotation Decorator
 *
 * Wraps any EncrypterInterface implementation to provide automatic
 * fallback to previous encryption keys during decryption. This enables
 * seamless key rotation without requiring re-encryption of existing data.
 */
class KeyRotationDecorator implements EncrypterInterface
{
    /**
     * @param EncrypterInterface $innerHandler The wrapped encryption handler
     * @param list<string>       $previousKeys Array of previous encryption keys
     */
    public function __construct(
        private readonly EncrypterInterface $innerHandler,
        private readonly array $previousKeys,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * Encryption always uses the inner handler's current key.
     */
    public function encrypt(#[SensitiveParameter] $data, #[SensitiveParameter] $params = null)
    {
        return $this->innerHandler->encrypt($data, $params);
    }

    /**
     * {@inheritDoc}
     *
     * Attempts decryption with current key first. If that fails and no
     * explicit key was provided in $params, tries each previous key.
     *
     * @throws EncryptionException
     */
    public function decrypt($data, #[SensitiveParameter] $params = null)
    {
        try {
            return $this->innerHandler->decrypt($data, $params);
        } catch (EncryptionException $e) {
            // Don't try previous keys if an explicit key was provided
            if (is_string($params) || (is_array($params) && isset($params['key']))) {
                throw $e;
            }

            if ($this->previousKeys === []) {
                throw $e;
            }

            foreach ($this->previousKeys as $previousKey) {
                try {
                    $previousParams = is_array($params)
                        ? array_merge($params, ['key' => $previousKey])
                        : $previousKey;

                    return $this->innerHandler->decrypt($data, $previousParams);
                } catch (EncryptionException) {
                    continue;
                }
            }

            throw $e;
        }
    }

    /**
     * Delegate property access to the inner handler.
     *
     * @return array|bool|int|string|null
     */
    public function __get(string $key)
    {
        if (method_exists($this->innerHandler, '__get')) {
            return $this->innerHandler->__get($key);
        }

        return null;
    }

    /**
     * Delegate property existence check to inner handler.
     */
    public function __isset(string $key): bool
    {
        if (method_exists($this->innerHandler, '__isset')) {
            return $this->innerHandler->__isset($key);
        }

        return false;
    }
}
