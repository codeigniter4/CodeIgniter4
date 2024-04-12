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

if ($argc !== 3) {
    echo "Usage: php {$argv[0]} <current_version> <new_version>" . PHP_EOL;
    echo "E.g.,: php {$argv[0]} 4.4.3 4.4.4" . PHP_EOL;

    exit(1);
}

// Gets version number from argument.
$versionCurrent      = $argv[1]; // e.g., '4.4.3'
$versionCurrentParts = explode('.', $versionCurrent);
$minorCurrent        = $versionCurrentParts[0] . '.' . $versionCurrentParts[1];
$version             = $argv[2]; // e.g., '4.4.4'
$versionParts        = explode('.', $version);
$minor               = $versionParts[0] . '.' . $versionParts[1];
$isMinorUpdate       = ($minorCurrent !== $minor);

// Creates a branch for release.
if (! $isMinorUpdate) {
    system('git switch develop');
}
system('git switch -c docs-changelog-' . $version);
system('git switch docs-changelog-' . $version);

// Copy changelog
$changelog      = "./user_guide_src/source/changelogs/v{$version}.rst";
$changelogIndex = './user_guide_src/source/changelogs/index.rst';
if ($isMinorUpdate) {
    copy('./admin/next-changelog-minor.rst', $changelog);
} else {
    copy('./admin/next-changelog-patch.rst', $changelog);
}
// Add changelog to index.rst.
replace_file_content(
    $changelogIndex,
    '/\.\. toctree::\n    :titlesonly:\n/u',
    ".. toctree::\n    :titlesonly:\n\n    v{$version}"
);
// Replace {version}
$length    = mb_strlen("Version {$version}");
$underline = str_repeat('#', $length);
replace_file_content(
    $changelog,
    '/#################\nVersion {version}\n#################/u',
    "{$underline}\nVersion {$version}\n{$underline}"
);
replace_file_content(
    $changelog,
    '/{version}/u',
    "{$version}"
);

// Copy upgrading
$versionWithoutDots = str_replace('.', '', $version);
$upgrading          = "./user_guide_src/source/installation/upgrade_{$versionWithoutDots}.rst";
$upgradingIndex     = './user_guide_src/source/installation/upgrading.rst';
copy('./admin/next-upgrading-guide.rst', $upgrading);
// Add upgrading to upgrading.rst.
replace_file_content(
    $upgradingIndex,
    '/    backward_compatibility_notes\n/u',
    "    backward_compatibility_notes\n\n    upgrade_{$versionWithoutDots}"
);
// Replace {version}
$length    = mb_strlen("Upgrading from {$versionCurrent} to {$version}");
$underline = str_repeat('#', $length);
replace_file_content(
    $upgrading,
    '/##############################\nUpgrading from {version} to {version}\n##############################/u',
    "{$underline}\nUpgrading from {$versionCurrent} to {$version}\n{$underline}"
);

// Commits
system("git add {$changelog} {$changelogIndex}");
system("git add {$upgrading} {$upgradingIndex}");
system('git commit -m "docs: add changelog and upgrade for v' . $version . '"');
