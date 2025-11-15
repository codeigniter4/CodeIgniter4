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
        __DIR__ . '/user_guide_src/source',
    ])
    ->notPath([
        'ci3sample/',
        'database/query_builder/075.php',
        'libraries/sessions/016.php',
        'outgoing/response/031.php',
        'outgoing/response/032.php',
    ]);

$overrides = [
    'echo_tag_syntax'              => false,
    'header_comment'               => false,
    'php_unit_internal_class'      => false,
    'no_unused_imports'            => false,
    'class_attributes_separation'  => false,
    'fully_qualified_strict_types' => [
        'import_symbols'                        => false,
        'leading_backslash_in_global_namespace' => true,
    ],
];

return $config
    ->setFinder($finder)
    ->setCacheFile('build/.php-cs-fixer.user-guide.cache')
    ->setRules(array_merge($config->getRules(), $overrides));
