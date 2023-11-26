<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Cache\FactoriesCache;

final class FileVarExportHandler
{
    private string $path = WRITEPATH . 'cache';

    /**
     * @param array|bool|float|int|object|string|null $val
     */
    public function save(string $key, $val): void
    {
        $val = var_export($val, true);

        // Write to temp file first to ensure atomicity
        $tmp = $this->path . "/{$key}." . uniqid('', true) . '.tmp';
        file_put_contents($tmp, '<?php return ' . $val . ';', LOCK_EX);

        rename($tmp, $this->path . "/{$key}");
    }

    public function delete(string $key): void
    {
        @unlink($this->path . "/{$key}");
    }

    /**
     * @return array|bool|float|int|object|string|null
     */
    public function get(string $key)
    {
        return @include $this->path . "/{$key}";
    }
}
