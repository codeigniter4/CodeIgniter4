<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

$baselineDir = __DIR__ . '/phpstan-baseline';

$files = glob($baselineDir . '/*.neon');
if ($files === false) {
    exit(1);
}

foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}

$loaderFile = $baselineDir . '/loader.neon';
if (! touch($loaderFile)) {
    echo "Error: Failed to create loader.neon.\n";

    exit(1);
}
