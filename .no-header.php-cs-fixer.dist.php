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
        __DIR__ . '/admin',
        __DIR__ . '/app',
        __DIR__ . '/public',
    ])
    ->notName('#Logger\.php$#');

$overrides = [
    // @TODO Remove once these are live in coding-standard
    'assign_null_coalescing_to_coalesce_equal' => false, // requires 7.4+
    'control_structure_continuation_position'  => ['position' => 'same_line'],
    'empty_loop_condition'                     => ['style' => 'while'],
    'integer_literal_case'                     => true,
    'modernize_strpos'                         => false, // requires 8.0+
    'no_alternative_syntax'                    => ['fix_non_monolithic_code' => false],
    'no_space_around_double_colon'             => true,
    'octal_notation'                           => false, // requires 8.1+
    'string_length_to_empty'                   => true,
];

$options = [
    'cacheFile'    => 'build/.no-header.php-cs-fixer.cache',
    'finder'       => $finder,
    'customFixers' => FixerGenerator::create('vendor/nexusphp/cs-config/src/Fixer', 'Nexus\\CsConfig\\Fixer'),
    'customRules'  => [
        NoCodeSeparatorCommentFixer::name() => true,
        SpaceAfterCommentStartFixer::name() => true,
    ],
];

return Factory::create(new CodeIgniter4(), $overrides, $options)->forProjects();
