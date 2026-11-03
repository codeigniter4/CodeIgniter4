<?php

declare(strict_types=1);

function replace_file_content(string $path, string $pattern, string $replace): void
{
    $file   = file_get_contents($path);
    $output = preg_replace($pattern, $replace, $file);
    file_put_contents($path, $output);
}

chdir(__DIR__ . '/..');

if (! isset($argv[1]) || ! isset($argv[2])) {
    echo "Usage: php {$argv[0]} <current_version> <new_version> [--dry-run]\n";
    echo "E.g. : php {$argv[0]} 4.4.3 4.4.4 --dry-run\n";

    exit(1);
}

// Gets version number from argument.
$currentVersion      = $argv[1]; // e.g., '4.4.3'
$currentVersionParts = explode('.', $currentVersion, 3);
$currentMinorVersion = $currentVersionParts[0] . '.' . $currentVersionParts[1];
$newVersion          = $argv[2]; // e.g., '4.4.4'
$newVersionParts     = explode('.', $newVersion, 3);
$newMinorVersion     = $newVersionParts[0] . '.' . $newVersionParts[1];
$isMinorUpdate       = $currentMinorVersion !== $newMinorVersion;

// Creates a branch for release
if (! in_array('--dry-run', $argv, true)) {
    if (! $isMinorUpdate) {
        system('git switch develop');
    }

    system("git switch -c docs-changelog-{$newVersion}");
    system("git switch docs-changelog-{$newVersion}");
}

// Copy changelog
$newChangelog   = "./docs/changelogs/v{$newVersion}.md";
$changelogIndex = './docs/changelogs/index.md';

if ($isMinorUpdate) {
    copy('./admin/next-changelog-minor.md', $newChangelog);
} else {
    copy('./admin/next-changelog-patch.md', $newChangelog);
}

// Replace version in CodeIgniter.php to {version}-dev.
replace_file_content(
    './system/CodeIgniter.php',
    '/public const CI_VERSION = \'.*?\';/u',
    "public const CI_VERSION = '{$newVersion}-dev';",
);

// Add changelog to the index, newest first.
replace_file_content(
    $changelogIndex,
    '/^(See all the changes\.\n\n)/mu',
    "\$1- [Version {$newVersion}](v{$newVersion}.md)\n",
);

// Replace {version}
replace_file_content($newChangelog, '/{version}/u', $newVersion);

// Copy upgrading
$versionWithoutDots = str_replace('.', '', $newVersion);
$newUpgrading       = "./docs/installation/upgrade_{$versionWithoutDots}.md";
$upgradingIndex     = './docs/installation/upgrading.md';
copy('./admin/next-upgrading-guide.md', $newUpgrading);

// Add upgrading to the index, right after the backward compatibility notes.
replace_file_content(
    $upgradingIndex,
    '/^(- \[Backward Compatibility Notes\]\(backward_compatibility_notes\.md\)\n)/mu',
    "\$1- [Upgrading from {$currentVersion} to {$newVersion}](upgrade_{$versionWithoutDots}.md)\n",
);

// Replace {version}
replace_file_content(
    $newUpgrading,
    '/^# Upgrading from {version} to {version}$/mu',
    "# Upgrading from {$currentVersion} to {$newVersion}",
);

// Add both pages to the nav.
system('php ./admin/update-docs-nav.php');

if (! in_array('--dry-run', $argv, true)) {
    system('git add ./system/CodeIgniter.php');
    system("git add {$newChangelog} {$changelogIndex}");
    system("git add {$newUpgrading} {$upgradingIndex} ./docs/.nav.yml");
    system("git commit -m \"docs: add changelog and upgrade for v{$newVersion}\"");
}
