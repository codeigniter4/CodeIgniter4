<?php

declare(strict_types=1);

/**
 * Checks that PRs merged since the last release carry the labels used to
 * generate the changelog, suggesting a label from the PR title type.
 *
 * Requires the authenticated GitHub CLI (`gh`).
 *
 * Usage: php admin/check-pr-labels.php [version]
 */
function color(string $text, string $code, bool $ansi): string
{
    return $ansi ? sprintf("\033[%sm%s\033[0m", $code, $text) : $text;
}

function hyperlink(string $text, string $url, bool $ansi): string
{
    return $ansi ? sprintf("\033]8;;%s\033\\%s\033]8;;\033\\", $url, $text) : $text;
}

function format_pull_line(array $pull, bool $ansi): string
{
    return sprintf(
        '* %s %s %s',
        hyperlink(color(sprintf('#%d', $pull['number']), '36', $ansi), $pull['url'], $ansi),
        $pull['title'],
        color(sprintf('(%s)', $pull['base']), '35', $ansi),
    );
}

function touches_system_dir(string $repo, int $number): bool
{
    exec(sprintf("gh api repos/%s/pulls/%d/files --paginate --jq '.[].filename' 2>&1", $repo, $number), $files, $exitCode);

    if ($exitCode !== 0) {
        return true;
    }

    foreach ($files as $file) {
        if (str_starts_with($file, 'system/')) {
            return true;
        }
    }

    return false;
}

chdir(__DIR__ . '/..');

$ansi = stream_isatty(STDOUT);
$repo = 'codeigniter4/CodeIgniter4';

// Labels used by the changelog categories. See ".github/release.yml".
$changelogLabels = ['breaking change', 'bug', 'enhancement', 'new feature', 'refactor'];

// PR title types that map to a changelog label.
$typeToLabel = [
    'fix'      => 'bug',
    'feat'     => 'new feature',
    'perf'     => 'enhancement',
    'refactor' => 'refactor',
];

// PR title types that need no changelog label.
$typesWithoutLabel = ['chore', 'ci', 'docs', 'style', 'test'];

// Release process PRs carry no labels.
$releaseTitles = '/\A(?:Prep for \d+\.\d+\.\d+ release|\d+\.\d+\.\d+ (?:Ready|Merge) code)\z/';

$tag = null;

foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/\Av?(\d+\.\d+\.\d+)\z/', $arg, $matches) === 1) {
        $tag = "v{$matches[1]}";

        continue;
    }

    echo sprintf("Usage: php %s [version]\n", $argv[0]);
    echo sprintf("E.g.,: php %s 4.7.3\n", $argv[0]);
    echo "Checks the PRs merged after the given release. Defaults to the latest release.\n";

    exit(1);
}

$endpoint = $tag === null ? sprintf('repos/%s/releases/latest', $repo) : sprintf('repos/%s/releases/tags/%s', $repo, $tag);
exec(sprintf('gh api %s 2>&1', $endpoint), $output, $exitCode);

if ($exitCode !== 0) {
    echo sprintf("Failed to fetch the release %s from GitHub:\n", $tag ?? '(latest)');
    echo implode("\n", $output) . "\n";

    exit(1);
}

$release = json_decode(implode("\n", $output), true);
$since   = $release['published_at'] ?? '';

echo sprintf("Checking PRs merged since %s (%s).\n", $release['tag_name'], $since);

$command = sprintf(
    'gh pr list --repo %s --search %s --json number,title,labels,baseRefName,url --limit 300 2>&1',
    $repo,
    escapeshellarg(sprintf('is:merged -base:master merged:>%s', $since)),
);
exec($command, $prOutput, $exitCode);

if ($exitCode !== 0) {
    echo "Failed to fetch the merged PRs from GitHub:\n";
    echo implode("\n", $prOutput) . "\n";

    exit(1);
}

$pulls = json_decode(implode("\n", $prOutput), true);

if (! is_array($pulls)) {
    echo "Unexpected response from GitHub.\n";

    exit(1);
}

echo sprintf("Found %d merged PRs.\n\n", count($pulls));

$missingLabel   = [];
$needManualLook = [];

foreach ($pulls as $pull) {
    $labels   = array_column($pull['labels'], 'name');
    $hasLabel = array_intersect($changelogLabels, $labels) !== [];
    $type     = null;

    if (preg_match('/\A(\w+)(?:\([^)]*\))?:/', $pull['title'], $matches) === 1) {
        $type = $matches[1];
    }

    if ($hasLabel || in_array($type, $typesWithoutLabel, true)) {
        continue;
    }

    if (preg_match($releaseTitles, $pull['title']) === 1) {
        continue;
    }

    if ($type !== null && isset($typeToLabel[$type])) {
        // The refactor label applies only to refactoring in system/. Test-only
        // refactors may use the testing label, which is not in the changelog.
        if ($type === 'refactor' && ! touches_system_dir($repo, (int) $pull['number'])) {
            continue;
        }

        $missingLabel[] = [
            'number' => $pull['number'],
            'title'  => $pull['title'],
            'base'   => $pull['baseRefName'],
            'url'    => $pull['url'],
            'label'  => $typeToLabel[$type],
        ];
    } else {
        $needManualLook[] = [
            'number' => $pull['number'],
            'title'  => $pull['title'],
            'base'   => $pull['baseRefName'],
            'url'    => $pull['url'],
        ];
    }
}

if ($missingLabel !== []) {
    echo color('PRs that appear to be missing a changelog label:', '1', $ansi) . "\n";

    foreach ($missingLabel as $pull) {
        echo sprintf("%s [suggested: %s]\n", format_pull_line($pull, $ansi), color($pull['label'], '33', $ansi));
    }

    echo "\nTo add the suggested labels, run the following commands (drop any that are not warranted):\n";

    foreach ($missingLabel as $pull) {
        echo sprintf("gh pr edit %d --repo %s --add-label \"%s\"\n", $pull['number'], $repo, $pull['label']);
    }

    echo "\n";
}

if ($needManualLook !== []) {
    echo color('PRs with no changelog label and no recognized title type (check manually):', '1', $ansi) . "\n";

    foreach ($needManualLook as $pull) {
        echo format_pull_line($pull, $ansi) . "\n";
    }

    echo "\n";
}

if ($missingLabel === []) {
    echo color('No PRs are missing changelog labels.', '32', $ansi) . "\n";

    exit(0);
}

exit(1);
