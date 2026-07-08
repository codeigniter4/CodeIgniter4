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

use JsonException;

/**
 * HTTP response for Server-Sent Events (SSE) streaming.
 *
 * @see \CodeIgniter\HTTP\SSEResponseTest
 */
class SSEResponse extends StreamResponse
{
    /**
     * Constructor.
     *
     * @param callable(SSEResponse): void $callback
     */
    public function __construct(callable $callback)
    {
        parent::__construct($callback);
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
        if (! $this->isClientConnected()) {
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
        return $this->write($this->formatMultiline('', $text));
    }

    /**
     * Set the client reconnection interval.
     *
     * @param int $milliseconds Retry interval in milliseconds
     */
    public function retry(int $milliseconds): bool
    {
        return $this->write("retry: {$milliseconds}\n\n");
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
            $output .= "{$prefix}: " . $line . "\n";
        }

        return $output . "\n";
    }

    /**
     * {@inheritDoc}
     *
     * SSE headers are fixed by the protocol, so they override
     * anything set on the response.
     */
    protected function prepareStreamHeaders(): void
    {
        $this->setContentType('text/event-stream', 'UTF-8');
        $this->removeHeader('Cache-Control');
        $this->setHeader('Cache-Control', 'no-cache');
        $this->setHeader('X-Accel-Buffering', 'no');

        // Connection: keep-alive is only valid for HTTP/1.x
        if (version_compare($this->getProtocolVersion(), '2.0', '<')) {
            $this->setHeader('Connection', 'keep-alive');
        }
    }

    /**
     * {@inheritDoc}
     *
     * CSP is not finalized for SSE responses, as Content Security
     * Policy does not apply to event streams.
     */
    protected function shouldFinalizeCsp(): bool
    {
        return false;
    }
}
