<?php namespace CodeIgniter\HTTP;

/**
 * Representation of an outgoing, server-side response.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - Status code and reason phrase
 * - Headers
 * - Message body
 *
 * @package CodeIgniter\HTTP
 */
interface ResponseInterface
{
	/**
	 * Gets the response status code.
	 *
	 * The status code is a 3-digit integer result code of the server's attempt
	 * to understand and satisfy the request.
	 *
	 * @return int Status code.
	 */
	public function getStatusCode(): int;

	//--------------------------------------------------------------------

	/**
	 * Return an instance with the specified status code and, optionally, reason phrase.
	 *
	 * If no reason phrase is specified, will default recommended reason phrase for
	 * the response's status code.
	 *
	 * @see http://tools.ietf.org/html/rfc7231#section-6
	 * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
	 *
	 * @param int    $code         The 3-digit integer result code to set.
	 * @param string $reasonPhrase The reason phrase to use with the
	 *                             provided status code; if none is provided, will
	 *                             default to the IANA name.
	 *
	 * @return self
	 * @throws \InvalidArgumentException For invalid status code arguments.
	 */
	public function setStatusCode(int $code, string $reason = ''): Response;

	//--------------------------------------------------------------------

	/**
	 * Gets the response response phrase associated with the status code.
	 *
	 * @see http://tools.ietf.org/html/rfc7231#section-6
	 * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
	 *
	 * @return string
	 */
	public function getReason(): string;

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Convenience Methods
	//--------------------------------------------------------------------

	/**
	 * Sets the date header
	 *
	 * @param \DateTime $date
	 *
	 * @return Response
	 */
	public function setDate(\DateTime $date): Response;

	//--------------------------------------------------------------------

	/**
	 * Sets the Content Type header for this response with the mime type
	 * and, optionally, the charset.
	 *
	 * @param string $mime
	 * @param string $charset
	 *
	 * @return Response
	 */
	public function setContentType(string $mime, string $charset='UTF-8'): Response;
	
	//--------------------------------------------------------------------
	
	
	//--------------------------------------------------------------------
	// Cache Control Methods
	//
	// http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
	//--------------------------------------------------------------------

	/**
	 * Sets the appropriate headers to ensure this response
	 * is not cached by the browsers.
	 */
	public function noCache(): Response;

	//--------------------------------------------------------------------

	/**
	 * A shortcut method that allows the developer to set all of the
	 * cache-control headers in one method call.
	 *
	 * The options array is used to provide the cache-control directives
	 * for the header. It might look something like:
	 *
	 *      $options = [
	 *          'max-age'  => 300,
	 *          's-maxage' => 900
	 *          'etag'     => 'abcde',
	 *      ];
	 *
	 * Typical options are:
	 *  - etag
	 *  - last-modified
	 *  - max-age
	 *  - s-maxage
	 *  - private
	 *  - public
	 *  - must-revalidate
	 *  - proxy-revalidate
	 *  - no-transform
	 *
	 * @param array $options
	 *
	 * @return $this
	 */
	public function setCache(array $options=[]): Response;

	//--------------------------------------------------------------------

	/**
	 * Sets the Last-Modified date header.
	 *
	 * $date can be either a string representation of the date or,
	 * preferably, an instance of DateTime.
	 *
	 * @param $date
	 */
	public function setLastModified($date): Response;

	//--------------------------------------------------------------------


	//--------------------------------------------------------------------
	// Output Methods
	//--------------------------------------------------------------------

	/**
	 * Sends the output to the browser.
	 *
	 * @return Response
	 */
	public function send(): Response;

	//--------------------------------------------------------------------

}
