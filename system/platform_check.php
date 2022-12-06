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

$issues = [];

$minPhpVersion   = '7.4.0';
$minPhpVersionId = 70400;

if ($minPhpVersionId > PHP_VERSION_ID) {
    $issues[] = sprintf(
        'Your PHP version must be PHP %s or higher to run CodeIgniter. Current version: PHP %s',
        $minPhpVersion,
        PHP_VERSION
    );
}

foreach (['intl', 'json', 'mbstring'] as $extension) {
    if (! extension_loaded($extension)) {
        $issues[] = sprintf(
            'The framework needs the following extension installed and loaded: %s.',
            $extension
        );
    }
}

if ($issues !== []) {
    if (! headers_sent()) {
        header('HTTP/1.1 500 Internal Server Error');
    }

    $preface = 'CodeIgniter cannot be booted because of issues detected in your platform:';

    if (! ini_get('display_errors')) {
        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            fwrite(STDERR, $preface . PHP_EOL . PHP_EOL . implode(PHP_EOL, $issues) . PHP_EOL . PHP_EOL);
        } elseif (! headers_sent()) {
            echo $preface . PHP_EOL . PHP_EOL . implode(PHP_EOL, $issues) . PHP_EOL . PHP_EOL;
        }
    }

    trigger_error($preface . PHP_EOL . implode(PHP_EOL, $issues), E_USER_ERROR);
}
