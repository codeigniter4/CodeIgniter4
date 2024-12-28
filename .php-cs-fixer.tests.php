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
        __DIR__ . '/tests',
    ])
    ->notPath([
        '_support/View/Cells/multiplier.php',
        '_support/View/Cells/colors.php',
        '_support/View/Cells/addition.php',
        'system/Database/Live/PreparedQueryTest.php',
    ])
    ->notName('#Foobar.php$#');

$overrides = [
    'void_return' => true,
];

return $config
    ->setFinder($finder)
    ->setCacheFile('build/.php-cs-fixer.tests.cache')
    ->setRules(array_merge($config->getRules(), $overrides));
