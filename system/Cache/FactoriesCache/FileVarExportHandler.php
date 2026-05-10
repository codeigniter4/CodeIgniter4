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

namespace CodeIgniter\Cache\FactoriesCache;

final class FileVarExportHandler
{
    private string $path = WRITEPATH . 'cache';

    public function save(string $key, mixed $val): void
    {
        $val = var_export($val, true);

        // Two processes may try to create the directory at the same time.
        // is_dir() confirms it exists, so suppressing the warning is safe.
        if (! is_dir($this->path) && ! @mkdir($this->path, 0777, true) && ! is_dir($this->path)) {
            log_message('error', 'FactoriesCache: cannot create cache directory: ' . $this->path);

            return;
        }

        // Write to temp file first to ensure atomicity
        $tmp = $this->path . "/{$key}." . uniqid('', true) . '.tmp';
        if (file_put_contents($tmp, '<?php return ' . $val . ';', LOCK_EX) === false) {
            log_message('warning', 'FactoriesCache: failed to write temp file for key: ' . $key);

            return;
        }

        // Another process may have wiped the directory. Clean up on failure.
        if (! @rename($tmp, $this->path . "/{$key}")) {
            log_message('warning', 'FactoriesCache: failed to commit cache file for key: ' . $key);

            @unlink($tmp);
        }
    }

    public function delete(string $key): void
    {
        @unlink($this->path . "/{$key}");
    }

    public function get(string $key): mixed
    {
        return @include $this->path . "/{$key}";
    }
}
