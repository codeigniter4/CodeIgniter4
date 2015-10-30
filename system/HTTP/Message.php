<?php namespace CodeIgniter\HTTP;

class Message
{
	/**
	 * List of all HTTP request headers
	 *
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

	protected $body;

	//--------------------------------------------------------------------
	
	//--------------------------------------------------------------------
	// Body
	//--------------------------------------------------------------------
	
	/**
	 * Returns the Message's body.
	 *
	 * @return mixed
	 */
	public function body()
	{
		return $this->body;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the body of the current message.
	 *
	 * @param $data
	 *
	 * @return Message
	 */
	public function setBody(&$data): self
	{
		$this->body = $data;

		return $this;
	}

	//--------------------------------------------------------------------
	
	//--------------------------------------------------------------------
	// Headers
	//--------------------------------------------------------------------
	
	/**
	 * Populates the $headers array with any headers the server knows about.
	 */
	public function populateHeaders()
	{
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

				if (array_key_exists($key, $_SERVER))
				{
					$this->setHeader($header, $_SERVER[$key]);
				}
				else
				{
					$this->setHeader($header, '');
				}
			}
		}
	}

	//--------------------------------------------------------------------


	/**
	 * Returns an array containing all headers.
	 *
	 * @return array        An array of the request headers
	 */
	public function headers() : array
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

	//--------------------------------------------------------------------

	/**
	 * Returns a single header.
	 *
	 * @param      $index
	 * @param null $filter
	 */
	public function header($name, $filter = null)
	{
		$orig_name = $this->getHeaderName($name);

		if ( ! isset($this->headers[$orig_name]))
		{
			return NULL;
		}

		if (is_null($filter))
		{
			$filter = FILTER_DEFAULT;
		}

		return is_array($this->headers[$orig_name])
			? filter_var_array($this->headers[$orig_name], $filter)
			: filter_var($this->headers[$orig_name], $filter);
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

		if (is_array($this->headers[$orig_name]) || $this->headers[$orig_name] instanceof \ArrayAccess)
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
	public function appendHeader(string $name, $value): self
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

	//--------------------------------------------------------------------
	// Content Negotiation
	//
	// @see http://tools.ietf.org/html/rfc7231#section-5.3
	//--------------------------------------------------------------------

	/**
	 * Determines the best content-type to use based on the $supported
	 * types the application says it supports, and the types requested
	 * by the client.
	 *
	 * If no match is found, the first, highest-ranking client requested
	 * type is returned.
	 *
	 * @param array $supported
	 *
	 * @return string
	 */
	public function negotiateMedia(array $supported): string
	{
		return $this->getBestMatch($supported, $this->header('accept'));
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the best charset to use based on the $supported
	 * types the application says it supports, and the types requested
	 * by the client.
	 *
	 * If no match is found, the first, highest-ranking client requested
	 * type is returned.
	 *
	 * @param array $supported
	 *
	 * @return string
	 */
	public function negotiateCharset(array $supported): string
	{
		return $this->getBestMatch($supported, $this->header('accept-charset'));
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the best encoding type to use based on the $supported
	 * types the application says it supports, and the types requested
	 * by the client.
	 *
	 * If no match is found, the first, highest-ranking client requested
	 * type is returned.
	 *
	 * @param array $supported
	 *
	 * @return string
	 */
	public function negotiateEncoding(array $supported): string
	{
		return $this->getBestMatch($supported, $this->header('accept-encoding'));
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the best language to use based on the $supported
	 * types the application says it supports, and the types requested
	 * by the client.
	 *
	 * If no match is found, the first, highest-ranking client requested
	 * type is returned.
	 *
	 * @param array $supported
	 *
	 * @return string
	 */
	public function negotiateLanguage(array $supported): string
	{
	    return $this->getBestMatch($supported, $this->header('accept-language'));
	}
	
	//--------------------------------------------------------------------
	
	//--------------------------------------------------------------------
	// Protected
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
		$lower_name = strtolower($name);

		return isset($this->headerMap[$lower_name]) ? $this->headerMap[$lower_name] : $name;
	}

	//--------------------------------------------------------------------

	/**
	 * Does the grunt work of comparing any of the app-supported values
	 * against a given Accept* header string.
	 *
	 * Portions of this code base on Aura.Accept library.
	 *
	 * @param array  $supported  App-supported values
	 * @param string $header     header string
	 *
	 * @return string Best match
	 */
	protected function getBestMatch(array $supported, string $header=null): string
	{
		if (empty($supported))
		{
			throw new \InvalidArgumentException('You must provide an array of supported values to all Negotiations.');
		}

		if (empty($header))
		{
			throw new \InvalidArgumentException('Header values must not be empty in Negotiations.');
		}

		$acceptable = $this->parseHeader($header);

		// If no acceptable values exist, return the
		// first that we support.
		if (empty($acceptable))
		{
			return $supported[0];
		}

		foreach ($acceptable as $accept)
		{
			// if acceptable quality is zero, skip it.
			if ($accept['q'] == 0)
			{
				continue;
			}

			// if acceptable value is "anything", return the first available
			if ($accept['value'] == '*' || $accept['value'] == '*/*')
			{
				return $supported[0];
			}

			// If an acceptable value is supported, return it
			foreach ($supported as $available)
			{
				if ($this->match($accept, $available))
				{
					return $available;
				}
			}
		}

		// No matches? return the first supported type
		return $supported[0];
	}

	//--------------------------------------------------------------------

	/**
	 * Parses an Accept* header into it's multiple values.
	 *
	 * This is based on code from Aura.Accept library.
	 *
	 * @param string $header
	 *
	 * @return array
	 */
	protected function parseHeader(string $header)
	{
		$results = [];
		$acceptable = explode(',', $header);

		foreach ($acceptable as $value)
		{
			$pairs = explode(';', $value);

			$value = $pairs[0];

			unset($pairs[0]);

			$parameters = array();

			foreach ($pairs as $pair)
			{
				$param = array();
				preg_match(
					'/^(?P<name>.+?)=(?P<quoted>"|\')?(?P<value>.*?)(?:\k<quoted>)?$/',
					$pair,
					$param
				);
				$parameters[trim($param['name'])] = trim($param['value']);
			}

			$quality = 1.0;

			if (array_key_exists('q', $parameters))
			{
				$quality = $parameters['q'];
				unset($parameters['q']);
			}

			$results[] = [
				'value' => trim($value),
				'q' => (float)$quality,
			    'params' => $parameters
			];
		}

		// Sort to get the highest results first
		usort($results, function ($a, $b)
		{
			if ($a['q'] == $b['q'])
			{
				// @ todo Modify to have more specificity be higher value...
				return 0;
			}

			return ($a['q'] > $b['q']) ? -1 : 1;
		});

		return $results;
	}

	//--------------------------------------------------------------------

	protected function match($acceptable, $supported)
	{
		$supported = $this->parseHeader($supported);
		if (is_array($supported) && count($supported) == 1)
		{
			$supported = $supported[0];
		}

		// Is it an exact match?
		if ($acceptable['value'] == $supported['value'])
		{
			return $this->matchParameters($acceptable, $supported);
		}



//		var_dump($acceptable);
//		die(var_dump($supported));
	}

	//--------------------------------------------------------------------

	protected function matchParameters($acceptable, $supported)
	{
		if (count($acceptable['params']) != count($supported['params']))
		{
			return false;
		}

		foreach ($supported['params'] as $label => $value)
		{
			if (! isset($acceptable['params'][$label]) ||
			    $acceptable['params'][$label] != $value)
			{
				return false;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------


}
