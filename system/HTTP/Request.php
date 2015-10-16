<?php namespace CodeIgniter\HTTP;

class Request implements RequestInterface
{
	/**
	 * Declares:
	 *
	 *      protected $protocolVersion;
	 *      protected $headers = [];
	 *      protected $body;
	 *      protected $isComplete;
	 *
	 *      public setHeader($name, $value);
	 *      public appendHeader($name, $value);
	 *      public removeHeader($name);
	 *      public hasHeader($name);
	 *      public getHeader($name);
	 *      public getHeaders();
	 *      public getProtocolVersion();
	 *      public setBody(&$data);
	 *      public getBody();
	 */
	use MessageTrait;

	protected $method;

	public $uri;

	protected $headers;

	protected $protocol_version;

	//--------------------------------------------------------------------

	public function __construct()
	{

	}

	//--------------------------------------------------------------------

	public function getStartLine(): string
	{
	    return $this->method.' '.$this->uri.' '.$this->protocolVersion;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves the HTTP method of the request.
	 *
	 * @return string Returns the request method.
	 */
	public function getMethod(): string
	{
		return $this->method;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the method used by this request.
	 *
	 * While HTTP method names are typically all uppercase characters, HTTP
	 * method names are case-sensitive and thus implementations SHOULD NOT
	 * modify the given string.
	 *
	 * @param string $method Case-sensitive method.
	 *
	 * @return self
	 * @throws \InvalidArgumentException for invalid HTTP methods.
	 */
	public function setMethod(string $method): RequestInterface
	{
		// Custom methods are allowed so we cannot restrict to
		// standard methods.
		$this->method = $method;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves the URI instance.
	 *
	 * This method MUST return a UriInterface instance.
	 *
	 * @see http://tools.ietf.org/html/rfc3986#section-4.3
	 *
	 * @return UriInterface Returns a UriInterface instance
	 *     representing the URI of the request.
	 */
	public function getURI(): URIInterface
	{
		return $this->uri;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the URI used
	 *
	 * @param URIInterface $uri
	 *
	 * @return RequestInterface
	 */
	public function setURI(URIInterface $uri): RequestInterface
	{
		$this->uri = $uri;

		return $this;
	}

	//--------------------------------------------------------------------
}