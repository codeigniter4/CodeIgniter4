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

namespace CodeIgniter\CLI;

use Override;

/**
 * An InputOutput sink that discards all output and never reads input.
 */
final class NullInputOutput extends InputOutput
{
    #[Override]
    public function fwrite($handle, string $string): void
    {
    }

    #[Override]
    public function input(?string $prefix = null): string
    {
        return '';
    }
}
