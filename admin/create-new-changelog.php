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
$newChangelog   = "./user_guide_src/source/changelogs/v{$newVersion}.rst";
$changelogIndex = './user_guide_src/source/changelogs/index.rst';

if ($isMinorUpdate) {
    copy('./admin/next-changelog-minor.rst', $newChangelog);
} else {
    copy('./admin/next-changelog-patch.rst', $newChangelog);
}

// Replace version in CodeIgniter.php to {version}-dev.
replace_file_content(
    './system/CodeIgniter.php',
    '/public const CI_VERSION = \'.*?\';/u',
    "public const CI_VERSION = '{$newVersion}-dev';",
);

// Add changelog to index.rst.
replace_file_content(
    $changelogIndex,
    '/\.\. toctree::\n    :titlesonly:\n/u',
    ".. toctree::\n    :titlesonly:\n\n    v{$newVersion}",
);

// Replace {version}
$underline = str_repeat('#', mb_strlen("Version {$newVersion}"));
replace_file_content(
    $newChangelog,
    '/#################\nVersion {version}\n#################/u',
    "{$underline}\nVersion {$newVersion}\n{$underline}",
);
replace_file_content($newChangelog, '/{version}/u', $newVersion);

// Copy upgrading
$versionWithoutDots = str_replace('.', '', $newVersion);
$newUpgrading       = "./user_guide_src/source/installation/upgrade_{$versionWithoutDots}.rst";
$upgradingIndex     = './user_guide_src/source/installation/upgrading.rst';
copy('./admin/next-upgrading-guide.rst', $newUpgrading);

// Add upgrading to upgrading.rst.
replace_file_content(
    $upgradingIndex,
    '/    backward_compatibility_notes\n/u',
    "    backward_compatibility_notes\n\n    upgrade_{$versionWithoutDots}",
);

// Replace {version}
$underline = str_repeat('#', mb_strlen("Upgrading from {$currentVersion} to {$newVersion}"));
replace_file_content(
    $newUpgrading,
    '/##############################\nUpgrading from {version} to {version}\n##############################/u',
    "{$underline}\nUpgrading from {$currentVersion} to {$newVersion}\n{$underline}",
);

if (! in_array('--dry-run', $argv, true)) {
    system('git add ./system/CodeIgniter.php');
    system("git add {$newChangelog} {$changelogIndex}");
    system("git add {$newUpgrading} {$upgradingIndex}");
    system("git commit -m \"docs: add changelog and upgrade for v{$newVersion}\"");
}
