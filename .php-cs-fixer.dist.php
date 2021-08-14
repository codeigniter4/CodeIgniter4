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
use Utils\PhpCsFixer\Fixer\Comment\SpaceAfterCommentStartFixer;

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

$overrides = [];

$options = [
    'cacheFile'    => 'build/.php-cs-fixer.cache',
    'finder'       => $finder,
    'customFixers' => [
        new SpaceAfterCommentStartFixer(),
    ],
    'customRules' => [
        'CodeIgniter4/space_after_comment_start' => true,
    ],
];

return Factory::create(new CodeIgniter4(), $overrides, $options)->forLibrary(
    'CodeIgniter 4 framework',
    'CodeIgniter Foundation',
    'admin@codeigniter.com'
);
