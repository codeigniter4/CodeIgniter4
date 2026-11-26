<?php

declare(strict_types=1);

use CodeIgniter\Debug\Backtrace;
use CodeIgniter\Debug\Inspection\CodeSnippet;
use CodeIgniter\Debug\Inspection\Frame;
use CodeIgniter\Debug\Inspection\Frames;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * @var string                $message Exception message before newline normalization.
 * @var string                $title   The exception class shown in the report heading.
 * @var string                $file    The exception's source path before stripping the project root.
 * @var int|null              $line
 * @var int                   $code
 * @var string                $reasonPhrase
 * @var array<string, string> $serverContext
 * @var string                $memoryUsage
 * @var IncomingRequest       $request
 * @var string                $requestPath
 * @var string                $requestUrl
 * @var string                $userAgent
 * @var list<Throwable>       $exceptionChain
 * @var Throwable             $rootCause
 * @var bool                  $showBacktrace
 */
$normalizedMessage = trim(str_replace(["\r\n", "\r"], "\n", $message));
$markdownTitle     = $normalizedMessage === '' ? $title : sprintf('[%s] %s', $title, $normalizedMessage);

$markdown = sprintf(
    <<<'EOT'
        # %s

        ## Exception

        - Source: %s
        - Status: HTTP %d %s

        ## Environment

        - PHP: %s
        - CodeIgniter: %s
        - Environment: %s
        - SAPI: %s
        - Time: %s
        - Memory Usage: %s

        EOT,
    $markdownTitle,
    Backtrace::location($file, $line),
    $code,
    $reasonPhrase,
    $serverContext['PHP'],
    $serverContext['CodeIgniter'],
    $serverContext['Environment'],
    $serverContext['SAPI'],
    $serverContext['Time'],
    $memoryUsage,
);

$markdown .= sprintf(
    <<<'EOT'

        ## Request

        - Method: %s
        - Path: %s
        - URL: %s
        - User Agent: %s

        EOT,
    $request->getMethod(),
    $requestPath,
    $requestUrl,
    $userAgent,
);

$causes = array_slice($exceptionChain, 1);

if ($causes !== []) {
    $markdown .= sprintf(
        <<<'EOT'

            ## Previous Exceptions

            - %s

            EOT,
        implode("\n- ", array_map(
            static fn (Throwable $e): string => sprintf(
                "[%s] %s\n  %s",
                $e::class,
                $e->getMessage(),
                Backtrace::location($e->getFile(), $e->getLine()),
            ),
            $causes,
        )),
    );
}

$sourceLocation = Frame::sourceLocation($rootCause->getFile(), $rootCause->getLine());
$excerptLine    = $sourceLocation['line'] ?? null;
$excerpt        = CodeSnippet::get($sourceLocation['file'], $excerptLine, highlight: false);

if ($excerpt !== []) {
    $excerptLines = [];
    $lineWidth    = strlen((string) array_key_last($excerpt));

    foreach ($excerpt as $number => $text) {
        $excerptLines[] = sprintf(
            '%s %*d | %s',
            $number === $excerptLine ? '>' : ' ',
            $lineWidth,
            $number,
            mb_scrub($text, 'UTF-8'),
        );
    }

    $markdown .= sprintf(
        <<<'EOT'

            ## Source Excerpt

            %s

            ```php
            %s
            ```

            EOT,
        Backtrace::location($sourceLocation['file'], $excerptLine),
        implode("\n", $excerptLines),
    );
}

if ($showBacktrace) {
    $markdown .= sprintf(
        <<<'EOT'

            ## Stack Trace

            ```text
            %s
            ```
            EOT,
        implode("\n", Backtrace::lines(Frames::fromException($rootCause), includeArguments: false)),
    );
}

printf("%s\n", esc(trim($markdown)));
