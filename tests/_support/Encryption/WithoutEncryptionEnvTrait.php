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

use CodeIgniter\Superglobals;
use Config\Services;

/**
 * Hides the local ".env" and the "encryption.*" values loaded from it.
 */
trait WithoutEncryptionEnvTrait
{
    public static function setUpBeforeClassWithoutEncryptionEnv(): void
    {
        $superglobals = new Superglobals();
        Services::injectMock('superglobals', $superglobals);

        if (! is_file(ROOTPATH . '.env')) {
            return;
        }

        rename(ROOTPATH . '.env', ROOTPATH . '.env.bak');

        foreach (array_keys($_ENV) as $key) {
            if (! str_starts_with($key, 'encryption.')) {
                continue;
            }

            putenv($key);
            unset($_ENV[$key]);
            $superglobals->unsetServer($key);
        }
    }

    public static function tearDownAfterClassWithoutEncryptionEnv(): void
    {
        Services::resetSingle('superglobals');

        if (is_file(ROOTPATH . '.env.bak')) {
            rename(ROOTPATH . '.env.bak', ROOTPATH . '.env');
        }
    }
}
