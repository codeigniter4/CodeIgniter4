<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/**
 * Checks the code style of the PHP samples in the user guide.
 *
 * Every ```php fence that starts with an opening tag and parses is run through
 * PHP CS Fixer with .php-cs-fixer.user-guide.php. A fence directly below a
 * `<!-- lint-ignore -->` line is skipped. With --fix, the fences are
 * rewritten in place. Otherwise, nothing is changed, and the script exits
 * with 1 when a sample needs fixing.
 *
 * Usage: php admin/lint-docs-samples.php [--fix] [--docs=<directory>]
 */

/**
 * @return list<string>
 */
function docs_pages(string $docs): array
{
    $pages    = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($docs, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS),
    );

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        $path = $file->getPathname();

        if ($file->getExtension() === 'md' && ! str_contains($path, '/overrides/')) {
            $pages[] = $path;
        }
    }

    sort($pages);

    return $pages;
}

function parses(string $code): bool
{
    try {
        token_get_all($code, TOKEN_PARSE);
    } catch (ParseError) {
        return false;
    }

    return true;
}

/**
 * @param list<string> $lines
 *
 * @return list<array{start: int, end: int, indent: string, code: string}>
 */
function find_samples(array $lines): array
{
    $samples = [];
    $count   = count($lines);

    for ($i = 0; $i < $count; $i++) {
        if (preg_match('/^(\s*)```php\s*$/', $lines[$i], $opening) !== 1) {
            continue;
        }

        $indent = $opening[1];
        $skip   = $i > 0 && trim($lines[$i - 1]) === '<!-- lint-ignore -->';

        for ($end = $i + 1; $end < $count; $end++) {
            if (rtrim($lines[$end]) === $indent . '```') {
                break;
            }
        }

        $body = array_map(
            static fn (string $line): string => str_starts_with($line, $indent)
                ? substr($line, strlen($indent))
                : ltrim($line),
            array_slice($lines, $i + 1, $end - $i - 1),
        );
        $code = implode("\n", $body) . "\n";

        if (! $skip && str_starts_with(ltrim($code), '<?php') && parses($code)) {
            $samples[] = ['start' => $i + 1, 'end' => $end, 'indent' => $indent, 'code' => $code];
        }

        $i = $end;
    }

    return $samples;
}

function remove_directory(string $directory): void
{
    foreach (glob($directory . '/*') ?: [] as $file) {
        unlink($file);
    }

    rmdir($directory);
}

$options = getopt('', ['fix', 'docs::']);
$fix     = isset($options['fix']);
$root    = dirname(__DIR__);
$docs    = rtrim(is_string($options['docs'] ?? null) ? $options['docs'] : $root . '/docs', '/');

$found = [];
$files = [];

foreach (docs_pages($docs) as $page) {
    $lines = explode("\n", (string) file_get_contents($page));

    foreach (find_samples($lines) as $sample) {
        $found[] = ['page' => $page, 'lines' => $lines] + $sample;
    }
}

$work = sys_get_temp_dir() . '/docs-samples-' . getmypid();
mkdir($work);

foreach ($found as $id => $sample) {
    $files[$id] = sprintf('%s/%05d.php', $work, $id);
    file_put_contents($files[$id], $sample['code']);
}

$command = sprintf(
    '%s %s %s --verbose --ansi --using-cache=no --config=%s %s 2>&1',
    escapeshellarg(PHP_BINARY),
    escapeshellarg($root . '/utils/vendor/bin/php-cs-fixer'),
    $fix ? 'fix' : 'check --diff',
    escapeshellarg($root . '/.php-cs-fixer.user-guide.php'),
    escapeshellarg($work),
);

exec($command, $output, $status);

if ($status !== 0 && ($fix || $status !== 8)) {
    fwrite(STDERR, implode("\n", $output) . "\n");
    remove_directory($work);

    exit(2);
}

if (! $fix && $status === 8) {
    $labels = [];

    foreach ($found as $id => $sample) {
        $labels[$files[$id]] = sprintf('%s:%d', substr($sample['page'], strlen($root) + 1), $sample['start'] + 1);
    }

    echo strtr(implode("\n", $output), $labels), "\n";
    remove_directory($work);

    exit(1);
}

$changed = [];

foreach ($found as $id => $sample) {
    $fixed = (string) file_get_contents($files[$id]);

    if ($fixed === $sample['code']) {
        continue;
    }

    $changed[$sample['page']][] = $sample + ['fixed' => $fixed, 'file' => $files[$id]];
}

if ($changed === []) {
    printf("\033[32m%s PHP samples checked, none need fixing.\033[0m\n", number_format(count($found)));
    remove_directory($work);

    exit(0);
}

$total = 0;

foreach ($changed as $page => $samples) {
    $lines = $samples[0]['lines'];

    foreach (array_reverse($samples) as $sample) {
        $total++;
        $body = array_map(
            static fn (string $line): string => $line === '' ? '' : $sample['indent'] . $line,
            explode("\n", rtrim($sample['fixed'], "\n")),
        );
        array_splice($lines, $sample['start'], $sample['end'] - $sample['start'], $body);
    }

    file_put_contents($page, implode("\n", $lines));
}

remove_directory($work);

printf("\033[32m%s of %s PHP samples fixed.\033[0m\n", number_format($total), number_format(count($found)));
