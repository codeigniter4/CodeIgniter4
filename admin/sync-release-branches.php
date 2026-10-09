<?php

declare(strict_types=1);

/**
 * Syncs the target branch with upstream and merges in the given source
 * branch, as done at several points of the release process.
 *
 * Usage: php admin/sync-release-branches.php <target> <source> [--push]
 */
function run_command(string $command): void
{
    echo sprintf("$ %s\n", $command);
    system($command, $exitCode);

    if ($exitCode !== 0) {
        exit($exitCode);
    }
}

function merge_branch(string $ref): void
{
    echo sprintf("$ git merge %s\n", $ref);
    system(sprintf('git merge %s', escapeshellarg($ref)), $exitCode);

    if ($exitCode !== 0) {
        echo sprintf("\nMerging %s failed. Resolve the conflicts and run \"git merge --continue\",\n", $ref);
        echo "then push with \"git push upstream HEAD\".\n";

        exit(1);
    }
}

chdir(__DIR__ . '/..');

$args    = array_slice($argv, 1);
$options = array_values(array_filter($args, static fn (string $arg): bool => str_starts_with($arg, '--')));
$params  = array_values(array_diff($args, $options));

$isBranch = static fn (string $name): bool => preg_match('/\A(?:develop|master|\d+\.\d+)\z/', $name) === 1;

if (
    count($params) !== 2
    || ! $isBranch($params[0])
    || ! $isBranch($params[1])
    || $params[0] === $params[1]
    || array_diff($options, ['--push']) !== []
) {
    echo sprintf("Usage: php %s <target> <source> [--push]\n", $argv[0]);
    echo sprintf("E.g.,: php %s 4.8 develop --push    # merges develop into 4.8\n", $argv[0]);
    echo sprintf("       php %s develop master --push # merges master into develop\n", $argv[0]);
    echo "Checks out <target>, merges upstream/<target> and then upstream/<source> into it.\n";
    echo "Without --push, the result is left unpushed for review.\n";

    exit(1);
}

[$target, $source] = $params;
$push              = in_array('--push', $options, true);

exec('git remote get-url upstream 2>&1', $remoteOutput, $exitCode);

if ($exitCode !== 0) {
    echo "The \"upstream\" remote is not configured.\n";

    exit(1);
}

exec('git status --porcelain 2>&1', $statusOutput, $exitCode);

if ($exitCode !== 0 || $statusOutput !== []) {
    echo "The working tree is not clean. Commit or stash your changes first.\n";

    exit(1);
}

run_command('git fetch upstream');

exec(sprintf('git rev-parse --verify --quiet refs/heads/%s 2>&1', escapeshellarg($target)), $verifyOutput, $exitCode);

if ($exitCode === 0) {
    run_command(sprintf('git switch %s', escapeshellarg($target)));
} else {
    run_command(sprintf('git switch -c %s upstream/%s', escapeshellarg($target), escapeshellarg($target)));
}

merge_branch("upstream/{$target}");
merge_branch("upstream/{$source}");

if ($push) {
    run_command('git push upstream HEAD');

    echo sprintf("Merged upstream/%s into %s and pushed to upstream.\n", $source, $target);
} else {
    echo sprintf("Merged upstream/%s into %s. Review the result, then run \"git push upstream HEAD\".\n", $source, $target);
}
