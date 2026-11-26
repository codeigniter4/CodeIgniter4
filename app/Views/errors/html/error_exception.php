<?php

declare(strict_types=1);

use CodeIgniter\CodeIgniter;
use CodeIgniter\Debug\Backtrace;
use CodeIgniter\Debug\ExceptionHandler;
use CodeIgniter\Debug\Inspection\CodeSnippet;
use CodeIgniter\Debug\Inspection\Frame;
use CodeIgniter\Debug\Inspection\Frames;
use CodeIgniter\Debug\Inspection\Inspector;
use CodeIgniter\HTTP\Header;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\MessageInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Router\Router;
use Kint\Renderer\AbstractRenderer;

/**
 * @var ExceptionHandler           $this
 * @var Throwable                  $exception
 * @var IncomingRequest            $request
 * @var ResponseInterface          $response
 * @var string                     $message
 * @var string                     $title
 * @var string                     $file
 * @var int|null                   $line
 * @var int                        $code
 * @var list<array<string, mixed>> $trace
 */
$exceptionChain = (new Inspector($exception))->chain();
$showBacktrace  = defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE;
$reasonPhrase   = $response->getStatusCode() === $code ? $response->getReasonPhrase() : '';

$requestPath = sprintf('/%s', ltrim($request->getPath(), '/'));
$requestUrl  = $requestPath;
$uri         = $request->getUri();

if ($uri->getHost() !== '') {
    $requestUrl = URI::createURIString(
        $uri->getScheme(),
        $uri->getPort() === null ? $uri->getHost() : sprintf('%s:%d', $uri->getHost(), $uri->getPort()),
        $requestPath,
    );
}

$userAgent = $this->redactDiagnosticData(['User-Agent' => $request->getUserAgent()->getAgentString()], caseInsensitive: true)['User-Agent'];

$serverContext = [
    'Environment' => ENVIRONMENT,
    'CodeIgniter' => CodeIgniter::CI_VERSION,
    'PHP'         => PHP_VERSION,
    'SAPI'        => PHP_SAPI,
    'Platform'    => PHP_OS_FAMILY,
    'Time'        => date('Y-m-d H:i:s e'),
];
helper('number');
$memoryUsage = number_to_size(memory_get_usage(true), 2);

$linkToFile = static function (?string $path, ?int $line = null, bool $compact = false): string {
    if ($path === null || $path === '') {
        return '[PHP internal code]';
    }

    $relative = Backtrace::relativePath($path);
    $basename = basename($relative);
    $location = sprintf('%s<strong>%s</strong>', esc(substr($relative, 0, strlen($relative) - strlen($basename))), esc($basename));
    $resolved = realpath($path);

    if ($resolved !== false && is_file($resolved)) {
        $normalized = is_windows() ? str_replace('\\', '/', $resolved) : $resolved;
        $encoded    = implode('/', array_map(rawurlencode(...), explode('/', $normalized)));
        $encoded    = preg_replace('/^([a-z])%3A/i', '/$1:', $encoded);

        $editorUrl = $line !== null && class_exists(AbstractRenderer::class)
            ? AbstractRenderer::getFileLink($encoded, $line)
            : null;
        $url      = $editorUrl === null || $editorUrl === '' ? sprintf('file://%s', $encoded) : $editorUrl;
        $location = sprintf('<a class="editor-link" href="%s">%s</a>', esc($url, 'attr'), $location);
    }

    if ($line === null) {
        return $location;
    }

    return sprintf($compact ? '%s:%d' : 'in %s (line %d)', $location, $line);
};

$throwSite    = $linkToFile($file, $line);
$stackHeading = $showBacktrace ? 'Stack trace' : 'Exceptions';
$heading      = $message === '' ? $title : $message;
$stack        = [];
$sourcePanels = [];
$snippets     = [];
$snippetLimit = 16;
$frameLimit   = 20;
$causeLimit   = 10;
$hiddenCauses = max(0, count($exceptionChain) - $causeLimit);
$rootCause    = $exceptionChain[array_key_last($exceptionChain)];

foreach (array_slice($exceptionChain, 0, $causeLimit) as $causeIndex => $cause) {
    if ($showBacktrace) {
        $causeTrace   = Frames::fromException($cause, $cause === $rootCause ? $trace : null);
        $hiddenFrames = max(0, count($causeTrace) - $frameLimit);
        $frames       = $causeTrace->take($frameLimit);
    } else {
        $location     = Frame::sourceLocation($cause->getFile(), $cause->getLine());
        $hiddenFrames = 0;
        $frames       = [new Frame('', $location['file'], $location['line'] ?? null)];
    }

    $stackFrames = [];

    foreach ($frames as $frameIndex => $frame) {
        $frameId = sprintf('frame-%02d%02d', $causeIndex, $frameIndex);
        $path    = $frame->file ?? '';
        $origin  = match (true) {
            str_starts_with($path, APPPATH)                             => 'Application',
            str_starts_with($path, SYSTEMPATH)                          => 'Framework',
            str_starts_with($path, FCPATH)                              => 'Front controller',
            defined('TESTPATH') && str_starts_with($path, TESTPATH)     => 'Application tests',
            defined('VENDORPATH') && str_starts_with($path, VENDORPATH) => 'Vendor',
            default                                                     => 'Root',
        };

        $stackFrames[] = [
            'id'       => $frameId,
            'call'     => $showBacktrace ? $frame->call() : 'Exception thrown',
            'location' => $linkToFile($frame->file, $frame->line, compact: true),
            'origin'   => $origin,
        ];

        $snippetKey = sprintf('%s:%d', $path, $frame->line ?? 0);

        if (
            $frame->file !== null
            && $frame->line !== null
            && ! isset($snippets[$snippetKey])
            // Limit source I/O even when an exception carries a deep trace.
            && count($snippets) < $snippetLimit
        ) {
            $snippets[$snippetKey] = CodeSnippet::get($frame->file, $frame->line);
        }

        $sourcePanels[$frameId] = [
            'location'  => $linkToFile($frame->file, $frame->line),
            'origin'    => $origin,
            'line'      => $frame->line,
            'source'    => $snippets[$snippetKey] ?? [],
            'arguments' => $showBacktrace ? $this->previewDiagnosticArguments($frame) : [],
        ];
    }

    $stack[] = [
        'cause'        => $causeIndex > 0 ? $cause : null,
        'frames'       => $stackFrames,
        'hiddenFrames' => $hiddenFrames,
    ];
}

$superglobals = service('superglobals');
$router       = service('router');

foreach (['SERVER_SOFTWARE', 'SERVER_PROTOCOL', 'SERVER_NAME', 'SERVER_PORT', 'REQUEST_SCHEME', 'GATEWAY_INTERFACE', 'SCRIPT_NAME', 'REQUEST_TIME'] as $key) {
    $value = $superglobals->server($key);

    if (is_string($value) || is_int($value)) {
        $serverContext[$key] = (string) $value;
    }
}

$constants = array_keys(get_defined_constants(true)['user'] ?? []);
sort($constants, SORT_STRING);

$requestContext = [
    'Method'   => $request->getMethod(),
    'Path'     => $requestPath,
    'URL'      => $requestUrl,
    'Protocol' => sprintf('HTTP/%s', $request->getProtocolVersion()),
];

if ($router instanceof Router) {
    $matchedRoute = $router->getMatchedRoute();

    if ($matchedRoute !== null) {
        $requestContext['Route'] = sprintf('/%s', ltrim($matchedRoute[0], '/'));
    }
}

$requestContext['Secure']     = $request->isSecure() ? 'Yes' : 'No';
$requestContext['User agent'] = $userAgent;

$responseContext = ['Status' => sprintf('%d %s', $code, $reasonPhrase)];

$memoryLimit   = ini_parse_quantity(ini_get('memory_limit'));
$memoryContext = [
    'Current usage' => $memoryUsage,
    'Peak usage'    => number_to_size(memory_get_peak_usage(true), 2),
    'Memory limit'  => $memoryLimit === -1 ? 'Unlimited' : number_to_size($memoryLimit, 2),
];

$headerContext = function (MessageInterface $message): array {
    $rows = [];

    // Show header values for debugging and mask credential headers by name.
    $sensitiveHeaders = [
        'authorization', 'proxy-authorization', 'cookie', 'cookie2', 'set-cookie', 'set-cookie2', 'api-key',
        'x-api-key', 'x-auth-token', 'x-csrf-token', 'x-xsrf-token',
    ];

    foreach ($message->headers() as $name => $headers) {
        $headers     = $headers instanceof Header ? [$headers] : $headers;
        $rows[$name] = implode(', ', array_map(static fn (Header $header): string => $header->getValueLine(), $headers));
    }

    return $this->redactDiagnosticData($rows, $sensitiveHeaders, caseInsensitive: true);
};

$sensitiveFields  = ['password', 'password_confirmation', 'token', 'access_token', 'refresh_token', 'secret', 'client_secret', 'api_key'];
$sensitiveCookies = [config('Session')->cookieName, sprintf('%s%s', config('Cookie')->prefix, config('Security')->cookieName)];
$requestDetails   = array_filter([
    'Headers'      => $headerContext($request),
    'Query fields' => $this->diagnosticFields($superglobals->getGetArray(), 'query', $sensitiveFields),
    'Form fields'  => $this->diagnosticFields($superglobals->getPostArray(), 'form', $sensitiveFields),
    'Cookies'      => $this->diagnosticFields($superglobals->getCookieArray(), 'cookies', [...$sensitiveFields, ...$sensitiveCookies]),
]);
$responseHeaders = $headerContext($response);

// Use the same markup for each field list, including the collapsed data.
$renderContext = static function (array $rows): void {
    ?>
    <dl class="diagnostic-values">
        <?php foreach ($rows as $name => $value) : ?>
            <dt><?= esc((string) $name) ?></dt>
            <dd><code><?= esc(is_array($value) ? (string) json_encode($value, JSON_INVALID_UTF8_SUBSTITUTE) : (string) $value) ?></code></dd>
        <?php endforeach ?>
    </dl>
    <?php
};

$includedFiles = get_included_files();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= esc($message) ?></title>
    <script>
        try {
            const theme = localStorage.getItem('ci-exception-theme');
            if (theme === 'light' || theme === 'dark') {
                document.documentElement.dataset.theme = theme;
            }
        } catch {
            // The system preference still applies when storage is unavailable.
        }
    </script>
    <style><?php readfile(__DIR__ . '/exception.css'); ?></style>
</head>
<body>
    <header class="environment">
        <div class="container environment-inner">
            <span class="framework">
                <!-- Same flame as the Debug Toolbar. -->
                <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" viewBox="0 0 155 200">
                    <path fill="#dd4814" d="M73.7 3.7c2.2 7.9-.7 18.5-7.8 29-1.8 2.6-10.7 12.2-19.7 21.3-23.9 24-33.6 37.1-40.3 54.4-7.9 20.6-7.8 40.8.5 58.2C12.8 180 27.6 193 42.5 198l6 2-3-2.2c-21-15.2-22.9-38.7-4.8-58.8 2.5-2.7 4.8-5 5.1-5 .4 0 .7 2.7.7 6.1 0 5.7.2 6.2 3.7 9.5 3 2.7 4.6 3.4 7.8 3.4 5.6 0 9.9-2.4 11.6-6.5 2.9-6.9 1.6-12-5-20.5-10.5-13.4-11.7-23.3-4.3-34.7l3.1-4.8.7 4.7c1.3 8.2 5.8 12.9 25 25.8 20.9 14.1 30.6 26.1 32.8 40.5 1.1 7.2-.1 16.1-3.1 21.8-2.7 5.3-11.2 14.3-16.5 17.4-2.4 1.4-4.3 2.6-4.3 2.8 0 .2 2.4-.4 5.3-1.4 24.1-8.3 42.7-27.1 48.2-48.6 1.9-7.6 1.9-20.2-.1-28.5-3.5-15.2-14.6-30.5-29.9-41.2l-7-4.9-.6 3.3c-.8 4.8-2.6 7.6-5.9 9.3-4.5 2.3-10.3 1.9-13.8-1-6.7-5.7-7.8-14.6-3.7-30.5 3-11.6 3.2-20.6.5-29.1C88.3 18 80.6 6.3 74.8 2.2 73.1.9 73 1 73.7 3.7z"/>
                </svg>
                CodeIgniter <?= esc(CodeIgniter::CI_VERSION) ?>
            </span>
            <div class="environment-controls">
                <span class="runtime">PHP <?= esc(PHP_VERSION) ?> &middot; <?= esc(ENVIRONMENT) ?></span>
                <button type="button" id="theme-toggle" class="theme-toggle" aria-label="Dark mode" aria-pressed="false" title="Switch to dark mode" hidden>
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path class="theme-moon" d="M20.9 13.35A9 9 0 0 1 10.65 3.1 9 9 0 1 0 20.9 13.35Z"/>
                        <g class="theme-sun">
                            <circle cx="12" cy="12" r="4"/>
                            <path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4m0-14.2-1.4 1.4M6.3 17.7l-1.4 1.4"/>
                        </g>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main>
        <header class="exception-summary">
            <div class="container summary-meta">
                <p class="exception-type"><?= esc($title) ?></p>
                <p class="http-status"><strong>HTTP <?= $code ?></strong> <?= esc($reasonPhrase) ?></p>
                <h1><?= esc($heading) ?></h1>
                <a class="copy-button" id="copy-details" href="#error-report" aria-live="polite">
                    <svg aria-hidden="true" class="copy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <g class="copy-icon-sheets">
                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                            <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                        </g>
                        <path class="copy-icon-check" d="m5 12 4 4L19 6" pathLength="1"/>
                    </svg>
                    <span class="copy-label">Copy Details</span>
                </a>
                <p class="throw-site"><?= $throwSite ?></p>
            </div>
        </header>

        <div class="container">
            <div class="exception-layout">
                <section class="stack" aria-labelledby="stack-heading">
                    <h2 id="stack-heading"><?= esc($stackHeading) ?></h2>
                    <?php foreach ($stack as ['cause' => $cause, 'frames' => $frames, 'hiddenFrames' => $hiddenFrames]) : ?>
                        <?php if ($cause !== null) : ?>
                            <details class="cause">
                                <summary>
                                    <span>Caused by</span>
                                    <strong><?= esc($cause::class) ?></strong>
                                    <span class="cause-message"><?= esc($cause->getMessage()) ?></span>
                                </summary>
                        <?php endif ?>

                        <ol class="frames">
                            <?php foreach ($frames as ['id' => $frameId, 'call' => $call, 'location' => $location, 'origin' => $origin]) : ?>
                                <li>
                                    <a class="frame-link" href="#<?= $frameId ?>" data-origin="<?= esc($origin, 'attr') ?>"<?php if ($frameId === 'frame-0000') : ?> aria-current="location"<?php endif ?>>
                                        <span class="frame-call"><?= esc($call) ?></span>
                                    </a>
                                    <span class="frame-location"><?= $location ?></span>
                                </li>
                            <?php endforeach ?>
                        </ol>

                        <?php if ($hiddenFrames > 0) : ?>
                            <p class="stack-overflow"><b>+<?= $hiddenFrames ?> hidden <?= $hiddenFrames === 1 ? 'frame' : 'frames' ?></b></p>
                        <?php endif ?>

                        <?php if ($cause !== null) : ?>
                            </details>
                        <?php endif ?>
                    <?php endforeach ?>

                    <?php if ($hiddenCauses > 0) : ?>
                        <p class="stack-overflow"><b>+<?= $hiddenCauses ?> hidden <?= $hiddenCauses === 1 ? 'exception' : 'exceptions' ?></b></p>
                    <?php endif ?>
                </section>

                <section class="source-panels" aria-label="Source">
                    <?php foreach ($sourcePanels as $frameId => ['location' => $location, 'origin' => $origin, 'line' => $sourceLine, 'source' => $source, 'arguments' => $arguments]) : ?>
                        <article class="source-panel" id="<?= $frameId ?>" tabindex="-1">
                            <header class="source-heading">
                                <h2><?= $location ?></h2>
                                <span><?= esc($origin) ?></span>
                            </header>

                            <?php if ($source === []) : ?>
                                <p class="source-unavailable">Source preview unavailable.</p>
                            <?php else : ?>
                                <pre class="source-code" tabindex="0"><code><?php
                                    foreach ($source as $number => $highlightedLine) {
                                        $lineClass = $number === $sourceLine ? ' current-line' : '';

                                        printf(
                                            "<span class=\"source-line%s\"><span class=\"line-number\" aria-hidden=\"true\">%d</span><span>%s</span></span>\n",
                                            $lineClass,
                                            $number,
                                            $highlightedLine,
                                        );
                                    }
                                ?></code></pre>
                            <?php endif ?>

                            <?php if ($arguments !== []) : ?>
                                <details class="frame-arguments">
                                    <summary>Arguments</summary>
                                    <dl class="diagnostic-values">
                                        <?php foreach ($arguments as $name => $value) : ?>
                                            <dt><code><b><?= esc(is_int($name) ? sprintf('#%d', $name + 1) : sprintf('$%s', $name)) ?></b></code></dt>
                                            <dd><pre class="argument-value"><code><?= esc($value) ?></code></pre></dd>
                                        <?php endforeach ?>
                                    </dl>
                                </details>
                            <?php endif ?>
                        </article>
                    <?php endforeach ?>
                </section>
            </div>

            <section class="diagnostics" aria-labelledby="diagnostics-heading">
                <h2 id="diagnostics-heading">Diagnostics</h2>

                <details class="diagnostic-section" open>
                    <summary>Request</summary>
                    <?php $renderContext($requestContext) ?>
                    <?php foreach ($requestDetails as $label => $rows) : ?>
                        <details class="diagnostic-fields">
                            <summary><?= esc($label) ?></summary>
                            <?php $renderContext($rows) ?>
                        </details>
                    <?php endforeach ?>
                </details>

                <details class="diagnostic-section" open>
                    <summary>Response</summary>
                    <?php $renderContext($responseContext) ?>
                    <?php if ($responseHeaders !== []) : ?>
                        <details class="diagnostic-fields">
                            <summary>Headers</summary>
                            <?php $renderContext($responseHeaders) ?>
                        </details>
                    <?php endif ?>
                </details>

                <details class="diagnostic-section">
                    <summary>Server</summary>
                    <?php $renderContext($serverContext) ?>
                    <?php if ($constants !== []) : ?>
                        <details class="diagnostic-fields">
                            <summary>Constants (<?= count($constants) ?>)</summary>
                            <ul class="diagnostic-files">
                                <?php foreach ($constants as $constant) : ?>
                                    <li><code><?= esc($constant) ?></code></li>
                                <?php endforeach ?>
                            </ul>
                        </details>
                    <?php endif ?>
                </details>

                <details class="diagnostic-section">
                    <summary>Files (<?= count($includedFiles) ?>)</summary>
                    <ol class="diagnostic-files">
                        <?php foreach ($includedFiles as $includedFile) : ?>
                            <li><code><?= $linkToFile($includedFile) ?></code></li>
                        <?php endforeach ?>
                    </ol>
                </details>

                <details class="diagnostic-section">
                    <summary>Memory</summary>
                    <?php $renderContext($memoryContext) ?>
                </details>
            </section>

            <footer class="exception-footer">
                <details id="error-report">
                    <summary>Error report</summary>
                    <label for="report-text">Review the exception details before sharing.</label>
                    <textarea id="report-text" readonly rows="10"><?php include __DIR__ . '/error_report.php'; ?></textarea>
                </details>
            </footer>
        </div>
    </main>
    <script>
        const themeToggle = document.getElementById('theme-toggle');
        const systemTheme = matchMedia('(prefers-color-scheme: dark)');
        const root = document.documentElement;

        function updateThemeToggle() {
            const dark = (root.dataset.theme ?? (systemTheme.matches ? 'dark' : 'light')) === 'dark';
            themeToggle.setAttribute('aria-pressed', String(dark));
            themeToggle.title = dark ? 'Switch to light mode' : 'Switch to dark mode';
        }

        themeToggle.addEventListener('click', () => {
            const theme = themeToggle.getAttribute('aria-pressed') === 'true' ? 'light' : 'dark';
            root.dataset.theme = theme;
            updateThemeToggle();

            try {
                localStorage.setItem('ci-exception-theme', theme);
            } catch {
                // The new theme still applies to this page.
            }
        });

        systemTheme.addEventListener('change', updateThemeToggle);
        updateThemeToggle();
        themeToggle.hidden = false;

        const copyDetails = document.getElementById('copy-details');
        const copyLabel = copyDetails.querySelector('.copy-label');
        const report = document.getElementById('error-report');
        const reportText = document.getElementById('report-text');
        let copyTimer;

        copyDetails.addEventListener('click', async (event) => {
            event.preventDefault();

            try {
                await navigator.clipboard.writeText(reportText.value);
                copyDetails.classList.add('is-copied');
                copyLabel.textContent = 'Copied';
                clearTimeout(copyTimer);
                copyTimer = setTimeout(() => {
                    copyDetails.classList.remove('is-copied');
                    copyLabel.textContent = 'Copy Details';
                }, 1500);
            } catch {
                report.open = true;
                reportText.focus();
                reportText.select();
            }
        });

        function markSelectedFrame() {
            const links = document.querySelectorAll('.frame-link');
            const current = Array.from(links).find((link) => link.hash === location.hash) ?? links[0];

            for (const link of links) {
                if (link === current) {
                    link.setAttribute('aria-current', 'location');
                } else {
                    link.removeAttribute('aria-current');
                }
            }
        }

        window.addEventListener('hashchange', markSelectedFrame);
        markSelectedFrame();
    </script>
</body>
</html>
