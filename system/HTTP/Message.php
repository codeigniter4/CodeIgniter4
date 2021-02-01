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

/**
 * An HTTP message
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
	];

	/**
	 * Message body
	 *
	 * @var mixed
	 */
	protected $body;

	//--------------------------------------------------------------------

	/**
	 * Returns the Message's body.
	 *
	 * @return mixed
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Returns an array containing all headers.
	 *
	 * @return array<string,Header> An array of the request headers
	 *
	 * @deprecated Use Message::headers() to make room for PSR-7
	 */
	public function getHeaders(): array
	{
		return $this->headers();
	}

	/**
	 * Returns a single header object. If multiple headers with the same
	 * name exist, then will return an array of header objects.
	 *
	 * @param string $name
	 *
	 * @return array|Header|null
	 *
	 * @deprecated Use Message::header() to make room for PSR-7
	 */
	public function getHeader(string $name)
	{
		return $this->header($name);
	}

	/**
	 * Determines whether a header exists.
	 *
	 * @param string $name
	 *
	 * @return boolean
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
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function getHeaderLine(string $name): string
	{
		$origName = $this->getHeaderName($name);

		if (! array_key_exists($origName, $this->headers))
		{
			return '';
		}

		return $this->headers[$origName]->getValueLine();
	}

	/**
	 * Returns the HTTP Protocol Version.
	 *
	 * @return string
	 */
	public function getProtocolVersion(): string
	{
		return $this->protocolVersion ?? '1.1';
	}

	/**
	 * Determines if this is a json message based on the Content-Type header
	 *
	 * @return boolean
	 *
	 * @deprecated Use header calls directly
	 */
      public function isJSON()
	{
		if (! $this->hasHeader('Content-Type'))
		{
			return false;
		}

		$header = $this->header('Content-Type')->getValue();
		$parts  = explode(';', $header);

		return in_array('application/json', $parts, true);
	}
}
