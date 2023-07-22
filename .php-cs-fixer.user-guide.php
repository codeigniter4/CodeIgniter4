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
        __DIR__ . '/user_guide_src/source',
    ])
    ->notPath([
        'ci3sample/',
        'libraries/sessions/016.php',
        'database/query_builder/075.php',
    ]);

$overrides = [
    'echo_tag_syntax'                    => false,
    'php_unit_internal_class'            => false,
    'no_unused_imports'                  => false,
    'class_attributes_separation'        => false,
    'php_unit_data_provider_return_type' => true,
];

$options = [
    'cacheFile' => 'build/.php-cs-fixer.user-guide.cache',
    'finder'    => $finder,
];

$config = Factory::create(new CodeIgniter4(), $overrides, $options)->forProjects();

// @TODO: remove this check when support for PHP 7.4 is dropped
if (PHP_VERSION_ID >= 80000) {
    $config
        ->registerCustomFixers(FixerGenerator::create('vendor/nexusphp/cs-config/src/Fixer', 'Nexus\\CsConfig\\Fixer'))
        ->setRules(array_merge($config->getRules(), [
            NoCodeSeparatorCommentFixer::name() => true,
        ]));
}

return $config;
