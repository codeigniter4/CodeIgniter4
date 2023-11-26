<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use InvalidArgumentException;

/**
 * Representation of an outgoing, client-side request.
 *
 * Corresponds to Psr7\RequestInterface.
 */
interface OutgoingRequestInterface extends MessageInterface
{
    /**
     * Get the request method.
     * An extension of PSR-7's getMethod to allow casing.
     *
     * @param bool $upper Whether to return in upper or lower case.
     *
     * @deprecated The $upper functionality will be removed and this will revert to its PSR-7 equivalent
     */
    public function getMethod(bool $upper = false): string;

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method Case-sensitive method.
     *
     * @return static
     *
     * @throws InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method);

    /**
     * Retrieves the URI instance.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @return URI
     */
    public function getUri();

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @param URI  $uri          New request URI to use.
     * @param bool $preserveHost Preserve the original state of the Host header.
     *
     * @return static
     */
    public function withUri(URI $uri, $preserveHost = false);
}
