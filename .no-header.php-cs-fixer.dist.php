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

use Nexus\CsConfig\Factory;
use PhpCsFixer\Finder;
use Utils\PhpCsFixer\CodeIgniter4;

$finder = Finder::create()
    ->files()
    ->in([
        __DIR__ . '/admin',
        __DIR__ . '/app',
        __DIR__ . '/public',
    ]);

$overrides = [];

$options = [
    'cacheFile' => 'build/.no-header.php-cs-fixer.cache',
    'finder'    => $finder,
];

return Factory::create(new CodeIgniter4(), $overrides, $options)->forProjects();
