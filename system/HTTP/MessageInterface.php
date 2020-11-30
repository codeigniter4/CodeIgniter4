<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;

/**
 * Expected behavior of an HTTP request
 */
interface MessageInterface
{
	/**
	 * Sets the body of the current message.
	 *
	 * @param mixed $data
	 *
	 * @return $this
	 */
	public function setBody($data);

	/**
	 * Appends data to the body of the current message.
	 *
	 * @param mixed $data
	 *
	 * @return $this
	 */
	public function appendBody($data);

	/**
	 * Populates the $headers array with any headers the getServer knows about.
	 */
	public function populateHeaders(): void;

	/**
	 * Returns an array containing all Headers.
	 *
	 * @return array<string,Header> An array of the Header objects
	 */
	public function headers(): array;

	/**
	 * Returns a single Header object. If multiple headers with the same
	 * name exist, then will return an array of header objects.
	 *
	 * @param string $name
	 *
	 * @return array|Header|null
	 */
	public function header($name);

	/**
	 * Sets a header and it's value.
	 *
	 * @param string            $name
	 * @param array|null|string $value
	 *
	 * @return $this
	 */
	public function setHeader(string $name, $value);

	/**
	 * Removes a header from the list of headers we track.
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function removeHeader(string $name);

	/**
	 * Adds an additional header value to any headers that accept
	 * multiple values (i.e. are an array or implement ArrayAccess)
	 *
	 * @param string      $name
	 * @param string|null $value
	 *
	 * @return $this
	 */
	public function appendHeader(string $name, ?string $value);

	/**
	 * Adds an additional header value to any headers that accept
	 * multiple values (i.e. are an array or implement ArrayAccess)
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return $this
	 */
	public function prependHeader(string $name, string $value);

	/**
	 * Sets the HTTP protocol version.
	 *
	 * @param string $version
	 *
	 * @return $this
	 *
	 * @throws HTTPException For invalid protocols
	 */
	public function setProtocolVersion(string $version);
}
