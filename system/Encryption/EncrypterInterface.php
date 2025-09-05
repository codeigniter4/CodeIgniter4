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
 * CodeIgniter Encryption Handler
 *
 * Provides two-way keyed encryption
 */
interface EncrypterInterface
{
    /**
     * Encrypt - convert plaintext into ciphertext
     *
     * @param string            $data   Input data
     * @param array|string|null $params Overridden parameters, specifically the key
     *
     * @return string
     *
     * @throws EncryptionException
     */
    public function encrypt(#[SensitiveParameter] $data, #[SensitiveParameter] $params = null);

    /**
     * Decrypt - convert ciphertext into plaintext
     *
     * @param string            $data   Encrypted data
     * @param array|string|null $params Overridden parameters, specifically the key
     *
     * @return string
     *
     * @throws EncryptionException
     */
    public function decrypt($data, #[SensitiveParameter] $params = null);
}
