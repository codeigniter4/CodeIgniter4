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

/**
 * HTTP response that streams its body to the client as it is generated,
 * instead of buffering the complete body first.
 *
 * @see \CodeIgniter\HTTP\StreamResponseTest
 */
class StreamResponse extends Response implements NonBufferedResponseInterface
{
    /**
     * @var Closure(static): void
     */
    private readonly Closure $callback;

    /**
     * @param (Closure(static): void)|iterable<string> $callbackOrChunks A callback that
     *                                                                   streams output via write(), or an iterable of
     *                                                                   string chunks to be written in order
     */
    public function __construct(Closure|iterable $callbackOrChunks)
    {
        parent::__construct();

        $this->callback = $callbackOrChunks instanceof Closure
            ? $callbackOrChunks
            : static function (self $response) use ($callbackOrChunks): void {
                foreach ($callbackOrChunks as $chunk) {
                    if (! $response->write($chunk)) {
                        break;
                    }
                }
            };
    }

    /**
     * Write a chunk of the streamed body.
     *
     * @param bool $flush Whether to flush output to the client immediately.
     *                    Pass false when writing many small chunks, then call
     *                    flush() at intervals.
     *
     * @return bool false if the client has disconnected
     */
    public function write(string $chunk, bool $flush = true): bool
    {
        if (! $this->isClientConnected()) {
            return false;
        }

        echo $chunk;

        if ($flush) {
            $this->flush();
        }

        return true;
    }

    /**
     * Flush buffered output to the client.
     */
    public function flush(): void
    {
        if (! service('environment')->isTesting()) {
            if (ob_get_level() > 0) {
                ob_flush();
            }

            flush();
        }
    }

    /**
     * Whether the client connection is still open.
     *
     * Note: PHP only detects a disconnect when attempting to write,
     * so this may return true until the next write() call fails.
     */
    public function isClientConnected(): bool
    {
        return connection_status() === CONNECTION_NORMAL && connection_aborted() === 0;
    }

    /**
     * {@inheritDoc}
     *
     * @return $this
     */
    public function send()
    {
        // Turn off output buffering completely, even if php.ini output_buffering is not off
        if (! service('environment')->isTesting()) {
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

        $this->prepareStreamHeaders();

        // Intentionally skip CSP finalize: the body is streamed, not buffered HTML.
        $this->sendHeaders();
        $this->sendCookies();

        ($this->callback)($this);

        return $this;
    }

    /**
     * Applies headers needed for unbuffered delivery, without overriding
     * any the developer has already set.
     */
    protected function prepareStreamHeaders(): void
    {
        if (! $this->hasHeader('X-Accel-Buffering')) {
            $this->setHeader('X-Accel-Buffering', 'no');
        }

        if (! $this->hasHeader('Content-Encoding')) {
            $this->setHeader('Content-Encoding', 'identity');
        }
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
