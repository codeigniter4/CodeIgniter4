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

namespace Utils;

require __DIR__ . '/../system/Test/bootstrap.php';

use CodeIgniter\CLI\CLI;

$rstFilesWithTabs = (string) shell_exec('git grep -EIPn "\t" -- "*.rst"');
$rstFilesWithTabs = explode("\n", $rstFilesWithTabs);
$rstFilesWithTabs = array_map(static function (string $line): array {
    preg_match('/^(?P<file>[^:]+):(?P<line>\d+):(?P<code>.+)$/', $line, $matches);

    return [
        'file' => $matches['file'],
        'line' => $matches['line'],
        'code' => $matches['code'],
    ];
}, array_filter($rstFilesWithTabs));

$normalizedRstFilesWithTabs = [];

foreach ($rstFilesWithTabs as ['file' => $file, 'line' => $line, 'code' => $code]) {
    if (! isset($normalizedRstFilesWithTabs[$file])) {
        $normalizedRstFilesWithTabs[$file] = [];
    }

    $normalizedRstFilesWithTabs[$file][] = ['line' => $line, 'code' => $code];
}

unset($rstFilesWithTabs);

if ($normalizedRstFilesWithTabs !== []) {
    printf(
        "%s\n\n%s\n",
        CLI::color('Tabs in RST files were detected:', 'light_gray', 'red'),
        implode("\n", array_map(
            static fn (string $file, array $parts): string => sprintf(
                "%s%s\n%s\n",
                CLI::color('* in ', 'light_red'),
                CLI::color($file, 'yellow'),
                implode("\n", array_map(static fn (array $line): string => sprintf(
                    '%s | %s',
                    str_pad($line['line'], 4, ' ', STR_PAD_LEFT),
                    str_replace("\t", CLI::color('....', 'light_gray', 'red'), $line['code']),
                ), $parts)),
            ),
            array_keys($normalizedRstFilesWithTabs),
            array_values($normalizedRstFilesWithTabs),
        )),
    );

    exit(1);
}

CLI::write('No tabs in RST files were detected.', 'black', 'green');

exit(0);
