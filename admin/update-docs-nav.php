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
 * Adds the pages of the user guide that are missing from its nav (docs/.nav.yml).
 *
 * A page goes into the section whose first page links to it, after the page
 * that precedes it in that list. With --check, nothing is changed, and the
 * script exits with 1 when a page is missing or a nav entry has no file.
 *
 * Usage: php admin/update-docs-nav.php [--check] [--docs=<directory>]
 */

/**
 * @return list<string> Markdown pages under $docs, relative to it
 */
function docs_pages(string $docs): array
{
    $pages    = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($docs, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        $page = str_replace('\\', '/', substr($file->getPathname(), strlen($docs) + 1));

        if ($file->getExtension() === 'md' && ! str_starts_with($page, 'overrides/')) {
            $pages[] = $page;
        }
    }

    sort($pages);

    return $pages;
}

/**
 * @param list<string> $lines
 *
 * @return array{indent: int, page: string|null, section: bool}|null
 */
function parse_nav_line(array $lines, int $i): ?array
{
    if (preg_match('/^(\s*)- (?:(?:"[^"]*"|[^:]+): )?([^\s:]+\.md)\s*$/', $lines[$i], $matches) === 1) {
        return ['indent' => strlen($matches[1]), 'page' => $matches[2], 'section' => false];
    }

    if (preg_match('/^(\s*)- (?:"[^"]*"|[^:]+):\s*$/', $lines[$i], $matches) === 1) {
        $head = isset($lines[$i + 1]) ? parse_nav_line($lines, $i + 1) : null;

        return ['indent' => strlen($matches[1]), 'page' => $head['page'] ?? null, 'section' => true];
    }

    return null;
}

/**
 * @param list<string> $lines
 *
 * @return list<string>
 */
function nav_pages(array $lines): array
{
    $pages = [];

    foreach (array_keys($lines) as $i) {
        $entry = parse_nav_line($lines, $i);

        if ($entry !== null && ! $entry['section']) {
            $pages[] = $entry['page'];
        }
    }

    return $pages;
}

/**
 * @return list<string> Pages that $page links to, in order, as paths relative to $docs
 */
function linked_pages(string $docs, string $page): array
{
    preg_match_all('/\]\(([^)#\s]+\.md)(?:#[^)]*)?\)/', (string) file_get_contents($docs . '/' . $page), $matches);

    $links = [];

    foreach ($matches[1] as $link) {
        if (str_contains($link, '://')) {
            continue;
        }

        $parts = [];

        foreach (explode('/', dirname($page) . '/' . $link) as $part) {
            if ($part === '..') {
                array_pop($parts);
            } elseif ($part !== '.' && $part !== '') {
                $parts[] = $part;
            }
        }

        $links[] = implode('/', $parts);
    }

    return array_values(array_unique($links));
}

/**
 * The index of the last line of the entry at $i, including the entries nested in it.
 *
 * @param list<string> $lines
 */
function entry_end(array $lines, int $i): int
{
    $indent = parse_nav_line($lines, $i)['indent'];
    $end    = $i;

    for ($j = $i + 1; $j < count($lines); $j++) {
        $entry = parse_nav_line($lines, $j);

        if ($entry === null || $entry['indent'] <= $indent) {
            break;
        }

        $end = $j;
    }

    return $end;
}

/**
 * Inserts $page into $lines, returning the title of the section it went into, or null when no section links to it.
 *
 * @param list<string> $lines
 */
function insert_page(array &$lines, string $docs, string $page): ?string
{
    $target = null;

    foreach (array_keys($lines) as $i) {
        $section = parse_nav_line($lines, $i);

        if ($section === null || ! $section['section'] || $section['page'] === null) {
            continue;
        }

        if (! in_array($page, linked_pages($docs, $section['page']), true)) {
            continue;
        }

        // The index of an outer section also lists the pages of the sections nested in it.
        if ($target === null || $section['indent'] > $target['indent']) {
            $target = $section + ['line' => $i];
        }
    }

    if ($target === null) {
        return null;
    }

    $order       = linked_pages($docs, $target['page']);
    $position    = array_search($page, $order, true);
    $after       = $target['line'] + 1;
    $childIndent = $target['indent'] + 4;
    $end         = entry_end($lines, $target['line']);

    for ($j = $target['line'] + 1; $j <= $end; $j++) {
        $entry = parse_nav_line($lines, $j);

        if ($entry === null || $entry['indent'] !== $childIndent || $entry['page'] === null) {
            continue;
        }

        $before = array_search($entry['page'], $order, true);

        if ($j === $target['line'] + 1 || ($before !== false && $before < $position)) {
            $after = entry_end($lines, $j);
        }
    }

    array_splice($lines, $after + 1, 0, [str_repeat(' ', $childIndent) . '- ' . $page]);

    return $target['page'];
}

$check = in_array('--check', $argv, true);
$docs  = 'docs';

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--docs=')) {
        $docs = rtrim(substr($argument, strlen('--docs=')), '/');
    }
}

$navFile = $docs . '/.nav.yml';

if (! is_file($navFile)) {
    echo "{$navFile} is not found.\n";

    exit(1);
}

$lines   = explode("\n", rtrim((string) file_get_contents($navFile), "\n"));
$pages   = docs_pages($docs);
$missing = array_values(array_diff($pages, nav_pages($lines)));
$stale   = array_values(array_diff(nav_pages($lines), $pages));

foreach ($stale as $page) {
    echo "The nav lists {$page}, which does not exist.\n";
}

foreach ($missing as $page) {
    if ($check) {
        echo "{$page} is missing from the nav.\n";

        continue;
    }

    $section = insert_page($lines, $docs, $page);

    if ($section === null) {
        $lines[] = '  - ' . $page;
        echo "Added {$page} at the end of the nav, since no section links to it. Move it by hand.\n";
    } else {
        echo "Added {$page} to the section of {$section}.\n";
    }
}

if (! $check && $missing !== []) {
    file_put_contents($navFile, implode("\n", $lines) . "\n");
}

if ($check && ($missing !== [] || $stale !== [])) {
    echo "Run \"php admin/update-docs-nav.php\" to update docs/.nav.yml.\n";
}

exit($check && ($missing !== [] || $stale !== []) ? 1 : 0);
