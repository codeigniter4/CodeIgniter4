<?php

declare(strict_types=1);

use Nexus\CsConfig\Factory;
use PhpCsFixer\Finder;
use Utils\PhpCsFixer\CodeIgniter4;

$finder = Finder::create()
    ->files()
    ->in([
        __DIR__ . '/admin',
        __DIR__ . '/app',
        __DIR__ . '/public',
    ]);

$overrides = [
	'no_blank_lines_after_phpdoc' => false,
];

$options = [
    'cacheFile' => 'build/.no-header.php-cs-fixer.cache',
    'finder'    => $finder,
];

return Factory::create(new CodeIgniter4(), $overrides, $options)->forProjects();
