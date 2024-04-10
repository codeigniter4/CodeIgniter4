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
        __DIR__ . '/tests',
    ])
    ->exclude([
    ])
    ->notPath([
        '_support/View/Cells/multiplier.php',
        '_support/View/Cells/colors.php',
        '_support/View/Cells/addition.php',
    ])
    ->notName('#Foobar.php$#')
    ->append([
    ]);

$overrides = [
    'void_return' => true,
];

$options = [
    'cacheFile' => 'build/.php-cs-fixer.tests.cache',
    'finder'    => $finder,
];

$config = Factory::create(new CodeIgniter4(), $overrides, $options)->forLibrary(
    'CodeIgniter 4 framework',
    'CodeIgniter Foundation',
    'admin@codeigniter.com'
);

$config
    ->registerCustomFixers(FixerGenerator::create('vendor/nexusphp/cs-config/src/Fixer', 'Nexus\\CsConfig\\Fixer'))
    ->setRules(array_merge($config->getRules(), [
        NoCodeSeparatorCommentFixer::name() => true,
    ]));

return $config;
