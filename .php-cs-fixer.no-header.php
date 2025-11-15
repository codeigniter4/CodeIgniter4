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

use PhpCsFixer\ConfigInterface;
use PhpCsFixer\Finder;

/** @var ConfigInterface $config */
$config = require __DIR__ . '/.php-cs-fixer.dist.php';

$finder = Finder::create()
    ->files()
    ->in([
        __DIR__ . '/admin',
        __DIR__ . '/app',
        __DIR__ . '/public',
    ])
    ->exclude(['Views/errors/html'])
    ->append([
        __DIR__ . '/admin/starter/builds',
    ]);

$overrides = [
    'header_comment' => false,
];

return $config
    ->setFinder($finder)
    ->setCacheFile('build/.php-cs-fixer.no-header.cache')
    ->setRules(array_merge($config->getRules(), $overrides));
