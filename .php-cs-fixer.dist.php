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
use Nexus\CsConfig\Fixer\Comment\SpaceAfterCommentStartFixer;
use Nexus\CsConfig\FixerGenerator;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->files()
    ->in([
        __DIR__ . '/system',
        __DIR__ . '/tests',
        __DIR__ . '/utils',
    ])
    ->exclude(['ThirdParty'])
    ->notName('#Foobar.php$#')
    ->append([
        __FILE__,
        __DIR__ . '/.no-header.php-cs-fixer.dist.php',
        __DIR__ . '/rector.php',
        __DIR__ . '/spark',
    ]);

$overrides = [
    // @TODO Remove once these are live in coding-standard
    'assign_null_coalescing_to_coalesce_equal' => false, // requires 7.4+
    'class_attributes_separation'              => [
        'elements' => [
            'const'        => 'none',
            'property'     => 'none',
            'method'       => 'one',
            'trait_import' => 'none',
        ],
    ],
    'control_structure_continuation_position' => ['position' => 'same_line'],
    'empty_loop_condition'                    => ['style' => 'while'],
    'integer_literal_case'                    => true,
    'modernize_strpos'                        => false, // requires 8.0+
    'no_alternative_syntax'                   => ['fix_non_monolithic_code' => false],
    'no_space_around_double_colon'            => true,
    'octal_notation'                          => false, // requires 8.1+
    'string_length_to_empty'                  => true,
];

$options = [
    'cacheFile'    => 'build/.php-cs-fixer.cache',
    'finder'       => $finder,
    'customFixers' => FixerGenerator::create('vendor/nexusphp/cs-config/src/Fixer', 'Nexus\\CsConfig\\Fixer'),
    'customRules'  => [
        NoCodeSeparatorCommentFixer::name() => true,
        SpaceAfterCommentStartFixer::name() => true,
    ],
];

return Factory::create(new CodeIgniter4(), $overrides, $options)->forLibrary(
    'CodeIgniter 4 framework',
    'CodeIgniter Foundation',
    'admin@codeigniter.com'
);
