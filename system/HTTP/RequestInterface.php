<?php namespace CodeIgniter\HTTP;

/**
 * Representation of an outgoing, client-side request.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - HTTP method
 * - URI
 *
 * The following are handled by implementors of this class,
 * but are provided by the MessageTrait:
 * - Protocol version
 * - Headers
 * - Message body
 *
 * During construction, implementations MUST attempt to set the Host header from
 * a provided URI if no Host header is provided.
 *
 * Requests are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 */
interface RequestInterface extends MessageInterface
{
	/**
	 * @param $protocolVersion
	 */
	public function __construct(HeaderCollection $headers, string $protocolVersion);

	//--------------------------------------------------------------------

	/**
	 * Retrieves the HTTP method of the request.
	 *
	 * @return string Returns the request method.
	 */
	public function getMethod(): string;

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
	public function setMethod(string $method): self;

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
	public function getURI(): URIInterface;

	//--------------------------------------------------------------------

	/**
	 * Sets the URI used
	 *
	 * @param URIInterface $uri
	 *
	 * @return RequestInterface
	 */
	public function setURI(URIInterface $uri): self;

	//--------------------------------------------------------------------

}
