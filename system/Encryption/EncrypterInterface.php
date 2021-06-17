<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Encryption;

use CodeIgniter\Encryption\Exceptions\EncryptionException;

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
     * @throws EncryptionException
     *
     * @return string
     */
    public function encrypt($data, $params = null);

    /**
     * Decrypt - convert ciphertext into plaintext
     *
     * @param string            $data   Encrypted data
     * @param array|string|null $params Overridden parameters, specifically the key
     *
     * @throws EncryptionException
     *
     * @return string
     */
    public function decrypt($data, $params = null);
}
