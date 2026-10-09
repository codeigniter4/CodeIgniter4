<?php

declare(strict_types=1);

/**
 * Generates the CHANGELOG.md entry for a new release from GitHub's
 * auto-generated release notes and prepends it to CHANGELOG.md.
 *
 * Requires the authenticated GitHub CLI (`gh`).
 *
 * Usage: php admin/generate-changelog.php <version> [--dry-run]
 */
chdir(__DIR__ . '/..');

$args    = array_slice($argv, 1);
$options = array_values(array_filter($args, static fn (string $arg): bool => str_starts_with($arg, '--')));
$params  = array_values(array_diff($args, $options));

if (count($params) !== 1 || preg_match('/\A\d+\.\d+\.\d+\z/', $params[0]) !== 1) {
    echo "Usage: php {$argv[0]} <version> [--dry-run]\n";
    echo "E.g.,: php {$argv[0]} 4.7.5\n";

    exit(1);
}

$version = $params[0];
$dryRun  = in_array('--dry-run', $options, true);
$repo    = 'codeigniter4/CodeIgniter4';

$changelogPath = './CHANGELOG.md';
$changelog     = file_get_contents($changelogPath);

if (! $dryRun && str_contains($changelog, "## [v{$version}]")) {
    echo "CHANGELOG.md already contains an entry for v{$version}.\n";

    exit(1);
}

// Fetches the auto-generated release notes.
$command = sprintf(
    'gh api repos/%s/releases/generate-notes -f tag_name=v%s -f target_commitish=develop 2>&1',
    $repo,
    $version,
);
exec($command, $output, $exitCode);

if ($exitCode !== 0) {
    echo "Failed to fetch the release notes from GitHub:\n";
    echo implode("\n", $output) . "\n";

    exit(1);
}

$response = json_decode(implode("\n", $output), true);
$body     = $response['body'] ?? '';

// Parses the release notes into categories. See ".github/release.yml".
$sections    = [];
$current     = null;
$previousTag = null;

foreach (preg_split('/\R/', $body) as $line) {
    if (preg_match('/^### (.+)$/', $line, $matches) === 1) {
        $current            = $matches[1];
        $sections[$current] = [];

        continue;
    }

    if (str_starts_with($line, '## ')) {
        $current = null;

        continue;
    }

    if (preg_match('~^\*\*Full Changelog\*\*: https://github\.com/[^/]+/[^/]+/compare/(.+?)\.\.\.~', $line, $matches) === 1) {
        $previousTag = $matches[1];

        continue;
    }

    if ($current !== null && str_starts_with($line, '* ')) {
        $sections[$current][] = $line;
    }
}

if ($previousTag === null) {
    echo "Could not determine the previous tag from the release notes.\n";

    exit(1);
}

// PRs in the catch-all category have none of the changelog labels. They are
// listed for checking only and are not part of the generated entry.
$othersTitle = 'Others (Only for checking. Remove this category)';
$others      = $sections[$othersTitle] ?? [];
unset($sections[$othersTitle]);

// Extracts the security fixes from the SECURITY section of the detailed
// changelog, if any. These come from security advisories, not from PRs, so
// they are not part of the generated release notes.
$rstPath  = "./user_guide_src/source/changelogs/v{$version}.rst";
$security = '';

if (is_file($rstPath)) {
    $rst = file_get_contents($rstPath);

    if (preg_match('/^\*+\nSECURITY\n\*+\n(.*?)(?=^\*+$|\z)/msu', $rst, $matches) === 1) {
        $security = trim($matches[1], "\n");
        $security = preg_replace('/^- /mu', '* ', $security);
    }
}

$date  = date('Y-m-d');
$entry = "## [v{$version}](https://github.com/{$repo}/tree/v{$version}) ({$date})\n";
$entry .= "[Full Changelog](https://github.com/{$repo}/compare/{$previousTag}...v{$version})\n";

if ($security !== '') {
    $entry .= "\n### Security\n\n{$security}\n";
}

foreach ($sections as $title => $items) {
    if ($items === []) {
        continue;
    }

    $entry .= "\n### {$title}\n\n";
    $entry .= implode("\n", $items) . "\n";
}

if ($others !== []) {
    echo "The following PRs have no changelog label and were NOT included.\n";
    echo "If any of them belong in the changelog, label the PR and run this script again:\n";
    echo implode("\n", $others) . "\n\n";
}

if ($dryRun) {
    echo $entry;

    exit(0);
}

$updated = preg_replace('/\A# Changelog\n\n/', "# Changelog\n\n{$entry}\n", $changelog, 1, $count);

if ($count !== 1) {
    echo 'Could not find the "# Changelog" header in CHANGELOG.md.' . "\n";

    exit(1);
}

file_put_contents($changelogPath, $updated);

echo "Added the v{$version} entry to CHANGELOG.md.\n";
