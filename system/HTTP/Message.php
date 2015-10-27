<?php namespace CodeIgniter\HTTP;

class Message
{
	/**
	 * List of all HTTP request headers
	 * @todo Allow for case-insensitive header access while retaining existing case.
	 * @var array
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

	protected $protocolVersion;

	protected $validProtocolVersions = ['1.0', '1.1'];

	//--------------------------------------------------------------------

	/**
	 * Returns an array containing all headers.
	 *
	 * @param null $filter
	 *
	 * @return array        An array of the request headers
	 */
	public function headers($filter = null) : array
	{
		// If header is already defined, return it immediately
		if ( ! empty($this->headers))
		{
			return $this->headers;
		}

		// In Apache, you can simply call apache_request_headers()
		if (function_exists('apache_request_headers'))
		{
			return $this->headers = apache_request_headers();
		}

		$this->headers['Content-Type'] = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : @getenv('CONTENT_TYPE');

		foreach ($_SERVER as $key => $val)
		{
			if (sscanf($key, 'HTTP_%s', $header) === 1)
			{
				// take SOME_HEADER and turn it into Some-Header
				$header = str_replace('_', ' ', strtolower($header));
				$header = str_replace(' ', '-', ucwords($header));

				$this->headers[$header] = $this->fetchGlobal(INPUT_SERVER, $key, $filter);
			}
		}

		return $this->headers;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a single header.
	 *
	 * @param      $index
	 * @param null $filter
	 */
	public function header($index, $filter = null)
	{
		static $headers;

		if ( ! isset($headers))
		{
			empty($this->headers) && $this->headers($filter);
			foreach ($this->headers as $key => $value)
			{
				$headers[strtolower($key)] = $value;
			}
		}

		$index = strtolower($index);

		if ( ! isset($headers[$index]))
		{
			return NULL;
		}

		if (is_null($filter))
		{
			$filter = FILTER_DEFAULT;
		}

		return filter_var($headers[$index], $filter);
	}

	//--------------------------------------------------------------------

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
	public function headerLine(string $name): string
	{
		$orig_name = $this->getHeaderName($name);

		if (! array_key_exists($orig_name, $this->headers))
		{
			return '';
		}

		if (is_array($this->headers) || $this->headers[$orig_name] instanceof \ArrayAccess)
		{
			return implode(', ', $this->headers[$orig_name]);
		}

		return (string)$this->headers[$orig_name];
	}

	//--------------------------------------------------------------------


	/**
	 * Sets a header and it's value.
	 *
	 * @param string $name
	 * @param        $value
	 *
	 * @return Message
	 */
	public function setHeader(string $name, $value): self
	{
		$this->headers[$name] = $value;

		$this->headerMap[strtolower($name)] = $name;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a header from the list of headers we track.
	 *
	 * @param string $name
	 *
	 * @return Message
	 */
	public function removeHeader(string $name): self
	{
		$orig_name = $this->getHeaderName($name);

		unset($this->headers[$orig_name]);
		unset($this->headerMap[strtolower($name)]);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds an additional header value to any headers that accept
	 * multiple values (i.e. are an array or implement ArrayAccess)
	 *
	 * @param string $name
	 * @param        $value
	 *
	 * @return string
	 */
	public function appendHeader(string $name, $value): string
	{
		$orig_name = $this->getHeaderName($name);

		if (! is_array($this->headers[$orig_name]) && ! ($this->headers[$orig_name] instanceof \ArrayAccess))
		{
			throw new \LogicException("Header '{$orig_name}' does not support multiple values.");
		}

		$this->headers[$orig_name][] = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the HTTP Protocol Version.
	 *
	 * @return string
	 */
	public function protocolVersion(): string
	{
	    return $this->protocolVersion;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the HTTP protocol version.
	 *
	 * @param string $version
	 *
	 * @return Message
	 */
	public function setProtocolVersion(string $version): self
	{
	    if (! in_array($version, $this->validProtocolVersions))
	    {
		    throw new \InvalidArgumentException('Invalid HTTP Protocol Version. Must be one of: '. implode(', ', $this->validProtocolVersions));
	    }

		$this->protocolVersion = $version;

		return $this;
	}
	
	//--------------------------------------------------------------------

	/**
	 * Takes a header name in any case, and returns the
	 * normal-case version of the header.
	 *
	 * @param $name
	 *
	 * @return string
	 */
	protected function getHeaderName($name): string
	{
		$name = strtolower($name);

	    return isset($this->headerMap[$name]) ? $this->headerMap[$name] : '';
	}

	//--------------------------------------------------------------------


}
