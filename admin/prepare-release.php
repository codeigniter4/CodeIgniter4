<?php

declare(strict_types=1);

function replace_file_content(string $path, string $pattern, string $replace): void
{
    $file   = file_get_contents($path);
    $output = preg_replace($pattern, $replace, $file);
    file_put_contents($path, $output);
}

// Main.
chdir(__DIR__ . '/..');

if ($argc !== 2) {
    echo "Usage: php {$argv[0]} <version>" . PHP_EOL;
    echo "E.g.,: php {$argv[0]} 4.4.3" . PHP_EOL;

    exit(1);
}

// Gets version number from argument.
$version      = $argv[1]; // e.g., '4.4.3'
$versionParts = explode('.', $version);
$minor        = $versionParts[0] . '.' . $versionParts[1];

// Creates a branch for release.
system('git switch develop');
system('git branch -D release-' . $version);
system('git switch -c release-' . $version);

// Updates version number in "CodeIgniter.php".
replace_file_content(
    './system/CodeIgniter.php',
    '/public const CI_VERSION = \'.*?\';/u',
    "public const CI_VERSION = '{$version}';"
);

// Updates version number in "conf.py".
replace_file_content(
    './user_guide_src/source/conf.py',
    '/^version = \'.*?\'/mu',
    "version = '{$minor}'"
);
replace_file_content(
    './user_guide_src/source/conf.py',
    '/^release = \'.*?\'/mu',
    "release = '{$version}'"
);

// Updates version number in "phpdoc.dist.xml".
replace_file_content(
    './phpdoc.dist.xml',
    '!<title>CodeIgniter v.*? API</title>!mu',
    "<title>CodeIgniter v{$minor} API</title>"
);
replace_file_content(
    './phpdoc.dist.xml',
    '/<version number=".*?">/mu',
    "<version number=\"{$version}\">"
);

// Updates release date in changelogs.
$date = date('F j, Y');
replace_file_content(
    "./user_guide_src/source/changelogs/v{$version}.rst",
    '/^Release Date: .*/mu',
    "Release Date: {$date}"
);

// Commits
system('git add -u');
system('git commit -m "Prep for ' . $version . ' release"');
