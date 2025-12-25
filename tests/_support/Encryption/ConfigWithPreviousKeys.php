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

namespace Tests\Support\Encryption;

use Config\Encryption as BaseEncryption;

/**
 * Encryption config for testing previousKeys functionality
 */
class ConfigWithPreviousKeys extends BaseEncryption
{
    public string $driver = 'OpenSSL';
    public string $key    = 'current-encryption-key-for-testing';

    /**
     * Previous encryption keys for decryption fallback
     *
     * @var list<string>|string
     */
    public array|string $previousKeys = [
        'old-key-1',
        'old-key-2',
    ];
}
