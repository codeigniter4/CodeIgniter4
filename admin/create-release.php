<?php

declare(strict_types=1);

/**
 * Creates the GitHub release for a new version, crediting every contributor
 * except bots.
 *
 * Requires the authenticated GitHub CLI (`gh`). Run it after the
 * "4.x.x Ready code" PR is merged into `master`.
 *
 * Usage: php admin/create-release.php <version> [--previous=<tag>] [--dry-run]
 */
chdir(__DIR__ . '/..');

$args    = array_slice($argv, 1);
$options = array_values(array_filter($args, static fn (string $arg): bool => str_starts_with($arg, '--')));
$params  = array_values(array_diff($args, $options));

if (count($params) !== 1 || preg_match('/\Av?(\d+\.\d+\.\d+)\z/', $params[0], $matches) !== 1) {
    echo sprintf("Usage: php %s <version> [--previous=<tag>] [--dry-run]\n", $argv[0]);
    echo sprintf("E.g.,: php %s 4.7.6\n", $argv[0]);

    exit(1);
}

$version = $matches[1];
$tag     = sprintf('v%s', $version);
$dryRun  = in_array('--dry-run', $options, true);
$repo    = 'codeigniter4/CodeIgniter4';

exec(sprintf('gh release view %s --repo %s 2>&1', escapeshellarg($tag), $repo), $output, $exitCode);

if ($exitCode === 0) {
    echo sprintf("The release %s already exists.\n", $tag);

    exit(1);
}

$previousTag = null;

foreach ($options as $option) {
    if (str_starts_with($option, '--previous=')) {
        $previousTag = sprintf('v%s', ltrim(substr($option, strlen('--previous=')), 'v'));
    }
}

if ($previousTag === null) {
    $output = [];
    exec(sprintf('gh release view --repo %s --json tagName --jq .tagName 2>&1', $repo), $output, $exitCode);

    if ($exitCode !== 0) {
        echo "Failed to fetch the latest release from GitHub:\n";
        echo implode("\n", $output) . "\n";

        exit(1);
    }

    $previousTag = trim(implode('', $output));
}

$output = [];
exec(sprintf(
    'gh api repos/%s/releases/generate-notes -f tag_name=%s -f target_commitish=master -f previous_tag_name=%s 2>&1',
    $repo,
    escapeshellarg($tag),
    escapeshellarg($previousTag),
), $output, $exitCode);

if ($exitCode !== 0) {
    echo "Failed to fetch the release notes from GitHub:\n";
    echo implode("\n", $output) . "\n";

    exit(1);
}

$notes = json_decode(implode("\n", $output), true)['body'] ?? '';

$contributors  = [];
$firstPulls    = [];
$fullChangelog = null;

foreach (preg_split('/\R/', $notes) as $line) {
    if (preg_match('~ by @(\S+) in https://github\.com/\S+/pull/\d+$~', $line, $matches) === 1) {
        $contributors[$matches[1]] = true;

        continue;
    }

    if (preg_match('~^\* @(\S+) made their first contribution in (\S+)$~', $line, $matches) === 1) {
        $firstPulls[$matches[1]] = $matches[2];

        continue;
    }

    if (str_starts_with($line, '**Full Changelog**: ')) {
        $fullChangelog = $line;
    }
}

if ($fullChangelog === null) {
    echo "Could not find the Full Changelog link in the release notes.\n";

    exit(1);
}

$contributors = array_filter(
    array_keys($contributors),
    static fn (string $login): bool => ! str_ends_with($login, '[bot]'),
);
natcasesort($contributors);

$credits = array_map(
    static fn (string $login): string => isset($firstPulls[$login])
        ? sprintf('* @%s made their first contribution in %s', $login, $firstPulls[$login])
        : sprintf('* @%s', $login),
    $contributors,
);

$body = sprintf("CodeIgniter %s release.\n\n", $version);
$body .= sprintf("See the changelog: https://github.com/%s/blob/develop/CHANGELOG.md\n\n", $repo);

if ($credits !== []) {
    $body .= sprintf("## Contributors\n\n%s\n\n", implode("\n", $credits));
}

$body .= $fullChangelog . "\n";

if ($dryRun) {
    echo $body;

    exit(0);
}

$notesFile = tempnam(sys_get_temp_dir(), 'ci4-release-');
file_put_contents($notesFile, $body);

passthru(sprintf(
    'gh release create %s --repo %s --target master --title %s --notes-file %s',
    escapeshellarg($tag),
    $repo,
    escapeshellarg(sprintf('CodeIgniter %s', $version)),
    escapeshellarg($notesFile),
), $exitCode);

unlink($notesFile);

exit($exitCode);
