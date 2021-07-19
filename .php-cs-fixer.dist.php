<?php

declare(strict_types=1);

use Nexus\CsConfig\Factory;
use PhpCsFixer\Finder;
use Utils\PhpCsFixer\CodeIgniter4;

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
    'cacheFile' => 'build/.php-cs-fixer.cache',
    'finder'    => $finder,
];

return Factory::create(new CodeIgniter4(), $overrides, $options)->forLibrary(
    'CodeIgniter 4 framework',
    'CodeIgniter Foundation',
    'admin@codeigniter.com'
);
