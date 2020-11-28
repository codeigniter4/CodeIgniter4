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
 * An HTTP message
 */
class Message
{
	/**
	 * List of all HTTP request headers.
	 *
	 * @var array<string,Header>
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
	// Body
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
	 * Sets the body of the current message.
	 *
	 * @param mixed $data
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
	 * @param mixed $data
	 *
	 * @return $this
	 */
	public function appendBody($data): self
	{
		$this->body .= (string) $data;

		return $this;
	}

	//--------------------------------------------------------------------
	// Headers
	//--------------------------------------------------------------------

	/**
	 * Populates the $headers array with any headers the getServer knows about.
	 */
	public function populateHeaders(): void
	{
		$contentType = $_SERVER['CONTENT_TYPE'] ?? getenv('CONTENT_TYPE');
		if (! empty($contentType))
		{
			$this->setHeader('Content-Type', $contentType);
		}
		unset($contentType);

		foreach ($_SERVER as $key => $val)
		{
			if (sscanf($key, 'HTTP_%s', $header) === 1)
			{
				// take SOME_HEADER and turn it into Some-Header
				$header = str_replace('_', ' ', strtolower($header));
				$header = str_replace(' ', '-', ucwords($header));

				$this->setHeader($header, $_SERVER[$key]);

				// Add us to the header map so we can find them case-insensitively
				$this->headerMap[strtolower($header)] = $header;
			}
		}
	}

	/**
	 * Returns an array containing all Headers.
	 *
	 * @return array<string,Header> An array of the Header objects
	 */
	public function headers(): array
	{
		// If no headers are defined, but the user is
		// requesting it, then it's likely they want
		// it to be populated so do that...
		if (empty($this->headers))
		{
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
	 * @return array|Header|null
	 */
	public function header($name)
	{
		$origName = $this->getHeaderName($name);

		if (! isset($this->headers[$origName]))
		{
			return null;
		}

		return $this->headers[$origName];
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
	 * Sets a header and it's value.
	 *
	 * @param string            $name
	 * @param array|null|string $value
	 *
	 * @return $this
	 */
	public function setHeader(string $name, $value): self
	{
		$origName = $this->getHeaderName($name);

		if (isset($this->headers[$origName]) && is_array($this->headers[$origName]->getValue()))
		{
			if (! is_array($value))
			{
				$value = [$value];
			}

			foreach ($value as $v)
			{
				$this->appendHeader($origName, $v);
			}
		}
		else
		{
			$this->headers[$origName]               = new Header($origName, $value);
			$this->headerMap[strtolower($origName)] = $origName;
		}

		return $this;
	}

	/**
	 * Removes a header from the list of headers we track.
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function removeHeader(string $name): self
	{
		$origName = $this->getHeaderName($name);

		unset($this->headers[$origName]);
		unset($this->headerMap[strtolower($name)]);

		return $this;
	}

	/**
	 * Adds an additional header value to any headers that accept
	 * multiple values (i.e. are an array or implement ArrayAccess)
	 *
	 * @param string      $name
	 * @param string|null $value
	 *
	 * @return $this
	 */
	public function appendHeader(string $name, ?string $value): self
	{
		$origName = $this->getHeaderName($name);

		array_key_exists($origName, $this->headers)
			? $this->headers[$origName]->appendValue($value)
			: $this->setHeader($name, $value);

		return $this;
	}

	/**
	 * Adds an additional header value to any headers that accept
	 * multiple values (i.e. are an array or implement ArrayAccess)
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return $this
	 */
	public function prependHeader(string $name, string $value): self
	{
		$origName = $this->getHeaderName($name);

		$this->headers[$origName]->prependValue($value);

		return $this;
	}

	/**
	 * Takes a header name in any case, and returns the
	 * normal-case version of the header.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	protected function getHeaderName(string $name): string
	{
		return $this->headerMap[strtolower($name)] ?? $name;
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
	 * Sets the HTTP protocol version.
	 *
	 * @param string $version
	 *
	 * @return $this
	 */
	public function setProtocolVersion(string $version): self
	{
		if (! is_numeric($version))
		{
			$version = substr($version, strpos($version, '/') + 1);
		}

		// Make sure that version is in the correct format
		$version = number_format((float) $version, 1);

		if (! in_array($version, $this->validProtocolVersions, true))
		{
			throw HTTPException::forInvalidHTTPProtocol(implode(', ', $this->validProtocolVersions));
		}

		$this->protocolVersion = $version;

		return $this;
	}

	/**
	 * Determines if this is a json message based on the Content-Type header
	 *
	 * @return boolean
	 */
	public function isJSON()
	{
		return $this->hasHeader('Content-Type')
			&& $this->header('Content-Type')->getValue() === 'application/json';
	}
}
