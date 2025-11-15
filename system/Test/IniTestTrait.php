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

namespace CodeIgniter\Test;

trait IniTestTrait
{
    private array $iniSettings = [];

    private function backupIniValues(array $keys): void
    {
        foreach ($keys as $key) {
            $this->iniSettings[$key] = ini_get($key);
        }
    }

    private function restoreIniValues(): void
    {
        foreach ($this->iniSettings as $key => $value) {
            ini_set($key, $value);
        }

        $this->iniSettings = [];
    }
}
