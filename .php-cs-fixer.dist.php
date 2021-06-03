<?php

declare(strict_types=1);

use Nexus\CsConfig\Factory;
use PhpCsFixer\Finder;
use Utils\PhpCsFixer\CodeIgniter4;

$finder = Finder::create()
    ->files()
    ->in([
        __DIR__ . '/admin', // @todo relocate when `header_comment` is enabled
        __DIR__ . '/app', // @todo relocate when `header_comment` is enabled
        __DIR__ . '/public', // @todo relocate when `header_comment` is enabled
        __DIR__ . '/system',
        __DIR__ . '/tests',
        __DIR__ . '/utils',
    ])
    ->exclude(['ThirdParty'])
    ->notName('#Foobar.php$#')
    ->append([
        __FILE__,
        __DIR__ . '/rector.php',
        __DIR__ . '/spark',
    ]);

$overrides = [];

$options = [
    'cacheFile' => 'build/.php-cs-fixer.cache',
    'finder'    => $finder,
];

// @todo change to `forLibrary()` when `header_comment` is enabled
return Factory::create(new CodeIgniter4(), $overrides, $options)->forProjects();
