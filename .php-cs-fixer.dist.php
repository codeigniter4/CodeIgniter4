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

use CodeIgniter\CodingStandard\CodeIgniter4;
use Nexus\CsConfig\Factory;
use Nexus\CsConfig\Fixer\Comment\NoCodeSeparatorCommentFixer;
use Nexus\CsConfig\FixerGenerator;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->files()
    ->in([
        __DIR__ . '/system',
        __DIR__ . '/tests',
        __DIR__ . '/utils',
    ])
    ->exclude([
        'Pager/Views',
        'ThirdParty',
        'Validation/Views',
    ])
    ->notPath([
        '_support/View/Cells/multiplier.php',
        '_support/View/Cells/colors.php',
        '_support/View/Cells/addition.php',
    ])
    ->notName('#Foobar.php$#')
    ->append([
        __FILE__,
        __DIR__ . '/.php-cs-fixer.no-header.php',
        __DIR__ . '/.php-cs-fixer.user-guide.php',
        __DIR__ . '/rector.php',
        __DIR__ . '/spark',
    ]);

$overrides = [
    'php_unit_data_provider_name' => [
        'prefix' => 'provide',
        'suffix' => '',
    ],
    'php_unit_data_provider_static'      => true,
    'php_unit_data_provider_return_type' => true,
    'no_extra_blank_lines'               => [
        'tokens' => [
            'attribute',
            'break',
            'case',
            'continue',
            'curly_brace_block',
            'default',
            'extra',
            'parenthesis_brace_block',
            'return',
            'square_brace_block',
            'switch',
            'throw',
            'use',
        ],
    ],
    'fully_qualified_strict_types' => [
        'import_symbols'                        => false,
        'leading_backslash_in_global_namespace' => false,
        'phpdoc_tags'                           => [
            'param',
            'phpstan-param',
            'phpstan-property',
            'phpstan-property-read',
            'phpstan-property-write',
            'phpstan-return',
            'phpstan-var',
            'property',
            'property-read',
            'property-write',
            'psalm-param',
            'psalm-property',
            'psalm-property-read',
            'psalm-property-write',
            'psalm-return',
            'psalm-var',
            'return',
            'throws',
            'var',
        ],
    ],
];

$options = [
    'cacheFile' => 'build/.php-cs-fixer.cache',
    'finder'    => $finder,
];

$config = Factory::create(new CodeIgniter4(), $overrides, $options)->forLibrary(
    'CodeIgniter 4 framework',
    'CodeIgniter Foundation',
    'admin@codeigniter.com'
);

// @TODO: remove this check when support for PHP 7.4 is dropped
if (PHP_VERSION_ID >= 80000) {
    $config
        ->registerCustomFixers(FixerGenerator::create('vendor/nexusphp/cs-config/src/Fixer', 'Nexus\\CsConfig\\Fixer'))
        ->setRules(array_merge($config->getRules(), [
            NoCodeSeparatorCommentFixer::name() => true,
        ]));
}

return $config;
