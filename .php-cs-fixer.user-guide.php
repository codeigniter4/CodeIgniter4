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

/** @var ConfigInterface $config */
$config = require __DIR__ . '/.php-cs-fixer.dist.php';

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
    ->setCacheFile('build/.php-cs-fixer.user-guide.cache')
    ->setRules(array_merge($config->getRules(), $overrides));
