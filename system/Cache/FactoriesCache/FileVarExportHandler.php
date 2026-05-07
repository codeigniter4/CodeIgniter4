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

        if (! is_dir($this->path) && ! @mkdir($this->path, 0777, true) && ! is_dir($this->path)) {
            return;
        }

        // Write to temp file first to ensure atomicity
        $tmp = $this->path . "/{$key}." . uniqid('', true) . '.tmp';
        if (file_put_contents($tmp, '<?php return ' . $val . ';', LOCK_EX) === false) {
            return;
        }

        if (! @rename($tmp, $this->path . "/{$key}")) {
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
