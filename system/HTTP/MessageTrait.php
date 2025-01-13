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

use CodeIgniter\HTTP\Exceptions\HTTPException;
use InvalidArgumentException;

/**
 * Message Trait
 * Additional methods to make a PSR-7 Message class
 * compliant with the framework's own MessageInterface.
 *
 * @see https://github.com/php-fig/http-message/blob/master/src/MessageInterface.php
 */
trait MessageTrait
{
    /**
     * List of all HTTP request headers.
     *
     * [name => Header]
     * or
     * [name => [Header1, Header2]]
     *
     * @var array<string, Header|list<Header>>
     */
    protected $headers = [];

    /**
     * Holds a map of lower-case header names
     * and their normal-case key as it is in $headers.
     * Used for case-insensitive header access.
     *
     * @var array
     */
    protected $headerMap = [];

    // --------------------------------------------------------------------
    // Body
    // --------------------------------------------------------------------

    /**
     * Sets the body of the current message.
     *
     * @param string $data
     *
     * @return $this
     */
    public function setBody($data): self
    {
        $this->body = $data;

        return $this;
    }

    /**
     * Appends data to the body of the current message.
     *
     * @param string $data
     *
     * @return $this
     */
    public function appendBody($data): self
    {
        $this->body .= (string) $data;

        return $this;
    }

    // --------------------------------------------------------------------
    // Headers
    // --------------------------------------------------------------------

    /**
     * Populates the $headers array with any headers the server knows about.
     */
    public function populateHeaders(): void
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? getenv('CONTENT_TYPE');
        if (! empty($contentType)) {
            $this->setHeader('Content-Type', $contentType);
        }
        unset($contentType);

        foreach (array_keys($_SERVER) as $key) {
            if (sscanf($key, 'HTTP_%s', $header) === 1) {
                // take SOME_HEADER and turn it into Some-Header
                $header = str_replace('_', ' ', strtolower($header));
                $header = str_replace(' ', '-', ucwords($header));

                $this->setHeader($header, $_SERVER[$key]);

                // Add us to the header map, so we can find them case-insensitively
                $this->headerMap[strtolower($header)] = $header;
            }
        }
    }

    /**
     * Returns an array containing all Headers.
     *
     * @return array<string, Header|list<Header>> An array of the Header objects
     */
    public function headers(): array
    {
        // If no headers are defined, but the user is
        // requesting it, then it's likely they want
        // it to be populated so do that...
        if (empty($this->headers)) {
            $this->populateHeaders();
        }

        return $this->headers;
    }

    /**
     * Returns a single Header object. If multiple headers with the same
     * name exist, then will return an array of header objects.
     *
     * @param string $name
     *
     * @return Header|list<Header>|null
     */
    public function header($name)
    {
        $origName = $this->getHeaderName($name);

        return $this->headers[$origName] ?? null;
    }

    /**
     * Sets a header and it's value.
     *
     * @param array|string|null $value
     *
     * @return $this
     */
    public function setHeader(string $name, $value): self
    {
        $this->checkMultipleHeaders($name);

        $origName = $this->getHeaderName($name);

        if (
            isset($this->headers[$origName])
            && is_array($this->headers[$origName]->getValue())
        ) {
            if (! is_array($value)) {
                $value = [$value];
            }

            foreach ($value as $v) {
                $this->appendHeader($origName, $v);
            }
        } else {
            $this->headers[$origName]               = new Header($origName, $value);
            $this->headerMap[strtolower($origName)] = $origName;
        }

        return $this;
    }

    private function hasMultipleHeaders(string $name): bool
    {
        $origName = $this->getHeaderName($name);

        return isset($this->headers[$origName]) && is_array($this->headers[$origName]);
    }

    private function checkMultipleHeaders(string $name): void
    {
        if ($this->hasMultipleHeaders($name)) {
            throw new InvalidArgumentException(
                'The header "' . $name . '" already has multiple headers.'
                . ' You cannot change them. If you really need to change, remove the header first.',
            );
        }
    }

    /**
     * Removes a header from the list of headers we track.
     *
     * @return $this
     */
    public function removeHeader(string $name): self
    {
        $origName = $this->getHeaderName($name);
        unset($this->headers[$origName], $this->headerMap[strtolower($name)]);

        return $this;
    }

    /**
     * Adds an additional header value to any headers that accept
     * multiple values (i.e. are an array or implement ArrayAccess)
     *
     * @return $this
     */
    public function appendHeader(string $name, ?string $value): self
    {
        $this->checkMultipleHeaders($name);

        $origName = $this->getHeaderName($name);

        array_key_exists($origName, $this->headers)
            ? $this->headers[$origName]->appendValue($value)
            : $this->setHeader($name, $value);

        return $this;
    }

    /**
     * Adds a header (not a header value) with the same name.
     * Use this only when you set multiple headers with the same name,
     * typically, for `Set-Cookie`.
     *
     * @return $this
     */
    public function addHeader(string $name, string $value): static
    {
        $origName = $this->getHeaderName($name);

        if (! isset($this->headers[$origName])) {
            $this->setHeader($name, $value);

            return $this;
        }

        if (! $this->hasMultipleHeaders($name) && isset($this->headers[$origName])) {
            $this->headers[$origName] = [$this->headers[$origName]];
        }

        // Add the header.
        $this->headers[$origName][] = new Header($origName, $value);

        return $this;
    }

    /**
     * Adds an additional header value to any headers that accept
     * multiple values (i.e. are an array or implement ArrayAccess)
     *
     * @return $this
     */
    public function prependHeader(string $name, string $value): self
    {
        $this->checkMultipleHeaders($name);

        $origName = $this->getHeaderName($name);

        $this->headers[$origName]->prependValue($value);

        return $this;
    }

    /**
     * Takes a header name in any case, and returns the
     * normal-case version of the header.
     */
    protected function getHeaderName(string $name): string
    {
        return $this->headerMap[strtolower($name)] ?? $name;
    }

    /**
     * Sets the HTTP protocol version.
     *
     * @return $this
     *
     * @throws HTTPException For invalid protocols
     */
    public function setProtocolVersion(string $version): self
    {
        if (! is_numeric($version)) {
            $version = substr($version, strpos($version, '/') + 1);
        }

        // Make sure that version is in the correct format
        $version = number_format((float) $version, 1);

        if (! in_array($version, $this->validProtocolVersions, true)) {
            throw HTTPException::forInvalidHTTPProtocol($version);
        }

        $this->protocolVersion = $version;

        return $this;
    }
}
