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

namespace CodeIgniter\HTTP;

use Closure;
use Config\App;
use JsonException;

/**
 * HTTP response for Server-Sent Events (SSE) streaming.
 *
 * @see \CodeIgniter\HTTP\SSEResponseTest
 */
class SSEResponse extends Response implements NonBufferedResponseInterface
{
    /**
     * Constructor.
     *
     * @param Closure(SSEResponse): void $callback
     */
    public function __construct(private readonly Closure $callback)
    {
        parent::__construct(config(App::class));
    }

    /**
     * Send an SSE event to the client.
     *
     * @param array<string, mixed>|string $data  Event data (arrays are JSON-encoded)
     * @param string|null                 $event Event type
     * @param string|null                 $id    Event ID
     */
    public function event(array|string $data, ?string $event = null, ?string $id = null): bool
    {
        if ($this->isConnectionAborted()) {
            return false;
        }

        $output = '';

        if ($event !== null) {
            $output .= 'event: ' . $this->sanitizeLine($event) . "\n";
        }

        if ($id !== null) {
            $output .= 'id: ' . $this->sanitizeLine($id) . "\n";
        }

        if (is_array($data)) {
            try {
                $data = json_encode($data, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                log_message('error', 'SSE JSON encode failed: {message}', ['message' => $e->getMessage()]);

                return false;
            }
        }

        $output .= $this->formatMultiline('data', $data);

        return $this->write($output);
    }

    /**
     * Send an SSE comment (useful for keep-alive).
     */
    public function comment(string $text): bool
    {
        if ($this->isConnectionAborted()) {
            return false;
        }

        return $this->write($this->formatMultiline('', $text));
    }

    /**
     * Set the client reconnection interval.
     *
     * @param int $milliseconds Retry interval in milliseconds
     */
    public function retry(int $milliseconds): bool
    {
        if ($this->isConnectionAborted()) {
            return false;
        }

        return $this->write("retry: {$milliseconds}\n\n");
    }

    /**
     * Check if the client connection has been lost.
     */
    private function isConnectionAborted(): bool
    {
        return connection_status() !== CONNECTION_NORMAL || connection_aborted() === 1;
    }

    /**
     * Strip newlines from a single-line SSE field (event, id).
     */
    private function sanitizeLine(string $value): string
    {
        return str_replace(["\r\n", "\r", "\n"], '', $value);
    }

    /**
     * Format a value as prefixed SSE lines, normalizing line endings.
     *
     * Each line becomes "{prefix}: {line}\n", terminated by an extra "\n".
     */
    private function formatMultiline(string $prefix, string $value): string
    {
        $value  = str_replace(["\r\n", "\r"], "\n", $value);
        $output = '';

        foreach (explode("\n", $value) as $line) {
            $output .= ($prefix !== '' ? "{$prefix}: " : ': ') . $line . "\n";
        }

        return $output . "\n";
    }

    /**
     * Write raw SSE output and flush.
     */
    private function write(string $output): bool
    {
        echo $output;

        if (ENVIRONMENT !== 'testing') {
            if (ob_get_level() > 0) {
                ob_flush();
            }

            flush();
        }

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @return $this
     */
    public function send()
    {
        // Turn off output buffering completely, even if php.ini output_buffering is not off
        if (ENVIRONMENT !== 'testing') {
            set_time_limit(0);
            ini_set('zlib.output_compression', 'Off');

            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }

        // Close session if active to prevent blocking other requests
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $this->setContentType('text/event-stream', 'UTF-8');
        $this->removeHeader('Cache-Control');
        $this->setHeader('Cache-Control', 'no-cache');
        $this->setHeader('X-Accel-Buffering', 'no');

        // Connection: keep-alive is only valid for HTTP/1.x
        if (version_compare($this->getProtocolVersion(), '2.0', '<')) {
            $this->setHeader('Connection', 'keep-alive');
        }

        // Intentionally skip CSP finalize: no HTML/JS execution in SSE streams.
        $this->sendHeaders();
        $this->sendCookies();

        ($this->callback)($this);

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * No-op — body is streamed via the callback, not stored.
     *
     * @return $this
     */
    public function sendBody()
    {
        return $this;
    }
}
