<?php

declare(strict_types=1);

/**
 * Updates the "Config" and "All Changes" sections of the version's upgrade
 * guide with the project space files changed since the last release.
 *
 * Usage: php admin/update-upgrade-guide.php <version> [--dry-run]
 */
function previous_release_tag(string $version): ?string
{
    // Not `git describe`: the base must be the highest stable tag below the
    // given version regardless of the checked out branch, and prerelease
    // tags (e.g. v4.0.0-rc.4) must be excluded.
    exec("git tag -l 'v*' 2>&1", $tags, $exitCode);

    if ($exitCode !== 0) {
        return null;
    }

    $tags = array_filter(
        $tags,
        static fn (string $tag): bool => preg_match('/\Av\d+\.\d+\.\d+\z/', $tag) === 1
            && version_compare($tag, "v{$version}", '<'),
    );

    if ($tags === []) {
        return null;
    }

    usort($tags, version_compare(...));

    return end($tags);
}

/**
 * @param list<string> $items
 */
function bullets(array $items): string
{
    return implode("\n", array_map(static fn (string $item): string => "- {$item}", $items));
}

chdir(__DIR__ . '/..');

$args    = array_slice($argv, 1);
$options = array_values(array_filter($args, static fn (string $arg): bool => str_starts_with($arg, '--')));
$params  = array_values(array_diff($args, $options));

if (count($params) !== 1 || preg_match('/\A\d+\.\d+\.\d+\z/', $params[0]) !== 1) {
    echo sprintf("Usage: php %s <version> [--dry-run]\n", $argv[0]);
    echo sprintf("E.g.,: php %s 4.7.5\n", $argv[0]);

    exit(1);
}

$version = $params[0];
$dryRun  = in_array('--dry-run', $options, true);

$upgradePath = sprintf('./user_guide_src/source/installation/upgrade_%s.rst', str_replace('.', '', $version));

if (! is_file($upgradePath)) {
    echo sprintf("%s is not found. Run \"php admin/create-new-changelog.php\" first.\n", $upgradePath);

    exit(1);
}

$baseTag = previous_release_tag($version);

if ($baseTag === null) {
    echo "Could not determine the previous release tag. Are the tags fetched?\n";

    exit(1);
}

echo sprintf("Checking the project space files changed since %s.\n", $baseTag);

// Paths that are not part of the project space. Keep in sync with the
// distribution repos: `tests/` is not used there, see "admin/starter/tests".
$excludes = [
    ':!.github/', ':!admin/', ':!changelogs/', ':!contributing/',
    ':!system/', ':!tests/', ':!user_guide_src/', ':!utils/',
    ':!*.json', ':!*.xml', ':!*.dist', ':!rector.php', ':!structarmed.php',
    ':!phpstan*', ':!psalm*', ':!.php-cs-fixer.*', ':!LICENSE', ':!CHANGELOG.md',
];

$command = sprintf(
    'git diff --name-status %s -- . %s 2>&1',
    escapeshellarg($baseTag),
    implode(' ', array_map(escapeshellarg(...), $excludes)),
);
exec($command, $diffOutput, $exitCode);

if ($exitCode !== 0) {
    echo "Failed to diff against the latest release tag:\n";
    echo implode("\n", $diffOutput) . "\n";

    exit(1);
}

$allChanges    = [];
$configChanged = [];
$configNew     = [];

foreach ($diffOutput as $line) {
    $fields = preg_split('/\t/', $line);

    if ($fields === false || count($fields) < 2) {
        continue;
    }

    $status = $fields[0][0];
    $path   = end($fields);

    $allChanges[] = $status === 'D' ? "{$path} (deleted)" : $path;

    if (! str_starts_with($path, 'app/Config/')) {
        continue;
    }

    if ($status === 'A') {
        $configNew[] = $path;
    } elseif ($status !== 'D') {
        $configChanged[] = $path;
    }
}

sort($allChanges);
sort($configChanged);
sort($configNew);

// Builds the "Config" section contents.
$configContent = bullets($configChanged);

if ($configNew !== []) {
    $configContent .= ($configContent === '' ? '' : "\n\n") . "These files are new in this release:\n\n" . bullets($configNew);
}

if ($configContent === '') {
    $configContent = '- No config files were changed in this release.';
}

// Builds the "All Changes" section contents.
$allChangesContent = $allChanges === []
    ? '- No project files were changed in this release.'
    : bullets($allChanges);

if ($dryRun) {
    echo "\nConfig\n------\n\n{$configContent}\n";
    echo "\nAll Changes\n===========\n\n{$allChangesContent}\n";

    exit(0);
}

$rst = file_get_contents($upgradePath);

// Fills the "Config" section, only if it still has the placeholder. Entries
// may have been added ahead of time for changes that need discussion, and
// those must not be overwritten.
if (preg_match('/^Config\n------\n\n(.*?)(?=^[^\s-][^\n]*\n(?:-+|=+)$|\z)/msu', $rst, $matches) !== 1) {
    echo "Could not find the \"Config\" section in {$upgradePath}. Update it manually.\n";
} elseif (trim($matches[1]) === '- @TODO') {
    $rst = str_replace("Config\n------\n\n{$matches[1]}", "Config\n------\n\n{$configContent}\n\n", $rst);

    echo "The \"Config\" section was updated. Add notes to each entry as needed.\n";
} else {
    $mentioned = [];

    if (preg_match_all('~app/Config/[\w./-]+~u', $matches[1], $found) > 0) {
        $mentioned = $found[0];
    }

    $missing = array_diff([...$configChanged, ...$configNew], $mentioned);

    if ($missing === []) {
        echo "The \"Config\" section already has content and mentions all changed config files. It was not modified.\n";
    } else {
        echo "The \"Config\" section already has content, so it was not modified.\n";
        echo "Merge these missing entries manually:\n" . bullets(array_values($missing)) . "\n";
    }
}

// Fills the "All Changes" section, replacing any previous list.
$pattern = '/^(All Changes\n===========\n\n.*?:\n\n)(.*?)(?=^\*+$|\z)/msu';
$updated = preg_replace($pattern, "\$1{$allChangesContent}\n", $rst, 1, $count);

if ($count !== 1) {
    echo "Could not find the \"All Changes\" section in {$upgradePath}. Update it manually.\n";

    exit(1);
}

file_put_contents($upgradePath, $updated);

echo sprintf("The \"All Changes\" section of %s was updated.\n", $upgradePath);
echo "Remember to remove the section titles that have no items.\n";
