<?php

use CodeIgniter\CodeIgniter;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;

$reportMessage = str_replace(["\r\n", "\r"], "\n", $message);
$reportTitle   = trim($reportMessage);
$reportTitle   = $reportTitle === '' ? $title : explode("\n", $reportTitle, 2)[0];
$messageLines  = str_contains($reportMessage, "\n");

$reportResponse = new Response();
$reportResponse->setStatusCode($code);

$report = [
    '# ' . $reportTitle,
    '',
    '## Exception',
    '',
    '- Type: ' . $type,
    '- Status Code: ' . $code,
    '- Status: ' . $reportResponse->getReasonPhrase(),
    $messageLines ? '- Message:' : '- Message: ' . $reportMessage,
];

if ($messageLines) {
    $report[] = '';
    $report[] = '```text';
    $report[] = $reportMessage;
    $report[] = '```';
}

$report[] = '';
$report[] = '## Environment';
$report[] = '';
$report[] = '- PHP: ' . PHP_VERSION;
$report[] = '- CodeIgniter: ' . CodeIgniter::CI_VERSION;
$report[] = '- Environment: ' . ENVIRONMENT;
$report[] = '- SAPI: ' . PHP_SAPI;
$report[] = '- Time: ' . date('Y-m-d H:i:s e');
$report[] = '- Memory Usage: ' . number_format(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';

$reportRequest = service('request');

if ($reportRequest instanceof IncomingRequest) {
    $reportPath = '/' . ltrim($reportRequest->getPath(), '/');
    $reportUri  = $reportRequest->getUri();
    $reportUrl  = $reportPath;

    if ($reportUri->getHost() !== '') {
        $reportUrl = URI::createURIString(
            $reportUri->getScheme(),
            $reportUri->getHost() . ($reportUri->getPort() === null ? '' : ':' . $reportUri->getPort()),
            $reportPath,
        );
    }

    $report[] = '';
    $report[] = '## Request';
    $report[] = '';
    $report[] = '- Method: ' . $reportRequest->getMethod();
    $report[] = '- Path: ' . $reportPath;
    $report[] = '- URL: ' . $reportUrl;
    $report[] = '- User Agent: ' . $reportRequest->getUserAgent()->getAgentString();
}

$report[] = '';
$report[] = '## Source';
$report[] = '';
$report[] = '`' . clean_path($file) . ':' . $line . '`';

if (is_file($file) && is_readable($file)) {
    $sourceLines = file($file, FILE_IGNORE_NEW_LINES);

    if ($sourceLines !== false) {
        $startLine = max($line - 5, 1);
        $endLine   = min($line + 5, count($sourceLines));

        $report[] = '';
        $report[] = '```php';

        for ($sourceLine = $startLine; $sourceLine <= $endLine; $sourceLine++) {
            $report[] = sprintf(
                '%s%4d %s',
                $sourceLine === $line ? '>' : ' ',
                $sourceLine,
                $sourceLines[$sourceLine - 1],
            );
        }

        $report[] = '```';
    }
}

$previousException = $exception->getPrevious();

if ($previousException instanceof Throwable) {
    $report[] = '';
    $report[] = '## Previous Exceptions';

    while ($previousException instanceof Throwable) {
        $report[] = '* ' . $previousException::class . ' - ' . $previousException->getMessage();
        $report[] = '  ' . clean_path($previousException->getFile()) . ':' . $previousException->getLine();

        $previousException = $previousException->getPrevious();
    }
}

if ($trace !== []) {
    $report[] = '';
    $report[] = '## Stack Trace';
    $report[] = '';
    $report[] = '```text';

    foreach (array_slice($trace, 0, 50) as $reportIndex => $reportRow) {
        $reportLocation = isset($reportRow['file'], $reportRow['line'])
            ? clean_path($reportRow['file']) . ':' . $reportRow['line']
            : '{PHP internal code}';
        $reportCall = ($reportRow['class'] ?? '') . ($reportRow['type'] ?? '') . ($reportRow['function'] ?? '');

        $report[] = $reportIndex . '  ' . $reportLocation . ($reportCall === '' ? '' : '  ' . $reportCall . '()');
    }

    $report[] = '```';
}

echo esc(implode("\n", $report)) . "\n";
