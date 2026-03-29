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

use CodeIgniter\Exceptions\InvalidArgumentException;

/**
 * An HTTP message
 *
 * @see \CodeIgniter\HTTP\MessageTest
 */
class Message implements MessageInterface
{
    use MessageTrait;

    /**
     * Protocol version
     *
     * @var string
     */
    protected $protocolVersion;

    /**
     * List of valid protocol versions
     *
     * @var array
     */
    protected $validProtocolVersions = [
        '1.0',
        '1.1',
        '2.0',
        '3.0',
    ];

    /**
     * Message body
     *
     * @var string|null
     */
    protected $body;

    /**
     * Returns the Message's body.
     *
     * @return string|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Determines whether a header exists.
     */
    public function hasHeader(string $name): bool
    {
        $origName = $this->getHeaderName($name);

        return isset($this->headers[$origName]);
    }

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
    public function getHeaderLine(string $name): string
    {
        if ($this->hasMultipleHeaders($name)) {
            throw new InvalidArgumentException(
                'The header "' . $name . '" already has multiple headers.'
                . ' You cannot use getHeaderLine().',
            );
        }

        $origName = $this->getHeaderName($name);

        if (! array_key_exists($origName, $this->headers)) {
            return '';
        }

        return $this->headers[$origName]->getValueLine();
    }

    /**
     * Returns the HTTP Protocol Version.
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion ?? '1.1';
    }
}
