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

/**
 * Expected behavior of an HTTP message
 */
interface MessageInterface
{
    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion(): string;

    /**
     * Sets the body of the current message.
     *
     * @param string $data
     *
     * @return $this
     */
    public function setBody($data);

    /**
     * Gets the body of the message.
     *
     * @return string|null
     *
     * @TODO Incompatible return type with PSR-7
     */
    public function getBody();

    /**
     * Appends data to the body of the current message.
     *
     * @param string $data
     *
     * @return $this
     */
    public function appendBody($data);

    /**
     * Populates the $headers array with any headers the server knows about.
     */
    public function populateHeaders(): void;

    /**
     * Returns an array containing all Headers.
     *
     * @return array<string, Header|list<Header>> An array of the Header objects
     */
    public function headers(): array;

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return bool Returns true if any header names match the given header
     *              name using a case-insensitive string comparison. Returns false if
     *              no matching header name is found in the message.
     */
    public function hasHeader(string $name): bool;

    /**
     * Returns a single Header object. If multiple headers with the same
     * name exist, then will return an array of header objects.
     *
     * @param string $name
     *
     * @return Header|list<Header>|null
     */
    public function header($name);

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     */
    public function getHeaderLine(string $name): string;

    /**
     * Sets a header and it's value.
     *
     * @param array|string|null $value
     *
     * @return $this
     */
    public function setHeader(string $name, $value);

    /**
     * Removes a header from the list of headers we track.
     *
     * @return $this
     */
    public function removeHeader(string $name);

    /**
     * Adds an additional header value to any headers that accept
     * multiple values (i.e. are an array or implement ArrayAccess)
     *
     * @return $this
     */
    public function appendHeader(string $name, ?string $value);

    /**
     * Adds an additional header value to any headers that accept
     * multiple values (i.e. are an array or implement ArrayAccess)
     *
     * @return $this
     */
    public function prependHeader(string $name, string $value);

    /**
     * Sets the HTTP protocol version.
     *
     * @return $this
     *
     * @throws HTTPException For invalid protocols
     */
    public function setProtocolVersion(string $version);
}
