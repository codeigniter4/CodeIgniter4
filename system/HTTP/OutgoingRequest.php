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

/**
 * Representation of an outgoing, client-side request.
 *
 * @see \CodeIgniter\HTTP\OutgoingRequestTest
 */
class OutgoingRequest extends Message implements OutgoingRequestInterface
{
    /**
     * Request method.
     *
     * @var string
     */
    protected $method;

    /**
     * A URI instance.
     *
     * @var URI|null
     */
    protected $uri;

    /**
     * @param string      $method HTTP method
     * @param string|null $body
     */
    public function __construct(
        string $method,
        ?URI $uri = null,
        array $headers = [],
        $body = null,
        string $version = '1.1'
    ) {
        $this->method = $method;
        $this->uri    = $uri;

        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value);
        }

        $this->body            = $body;
        $this->protocolVersion = $version;

        if (! $this->hasHeader('Host') && $this->uri->getHost() !== '') {
            $this->setHeader('Host', $this->getHostFromUri($this->uri));
        }
    }

    private function getHostFromUri(URI $uri): string
    {
        $host = $uri->getHost();

        return $host . ($uri->getPort() ? ':' . $uri->getPort() : '');
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method (always uppercase)
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Sets the request method. Used when spoofing the request.
     *
     * @return $this
     *
     * @deprecated Use withMethod() instead for immutability
     */
    public function setMethod(string $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Returns an instance with the specified method.
     *
     * @param string $method
     *
     * @return static
     */
    public function withMethod($method)
    {
        $request         = clone $this;
        $request->method = $method;

        return $request;
    }

    /**
     * Retrieves the URI instance.
     *
     * @return URI|null
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * @param URI  $uri          New request URI to use.
     * @param bool $preserveHost Preserve the original state of the Host header.
     *
     * @return static
     */
    public function withUri(URI $uri, $preserveHost = false)
    {
        $request      = clone $this;
        $request->uri = $uri;

        if ($preserveHost) {
            if ($this->isHostHeaderMissingOrEmpty() && $uri->getHost() !== '') {
                $request->setHeader('Host', $this->getHostFromUri($uri));

                return $request;
            }

            if ($this->isHostHeaderMissingOrEmpty() && $uri->getHost() === '') {
                return $request;
            }

            if (! $this->isHostHeaderMissingOrEmpty()) {
                return $request;
            }
        }

        if ($uri->getHost() !== '') {
            $request->setHeader('Host', $this->getHostFromUri($uri));
        }

        return $request;
    }

    private function isHostHeaderMissingOrEmpty(): bool
    {
        if (! $this->hasHeader('Host')) {
            return true;
        }

        return $this->header('Host')->getValue() === '';
    }
}
