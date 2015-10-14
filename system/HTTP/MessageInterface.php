<?php namespace CodeIgniter\HTTP;

/**
 * HTTP messages consist of requests from a client to a server and responses
 * from a server to a client. This interface defines the methods common to
 * each.
 *
 * @see http://www.ietf.org/rfc/rfc7230.txt
 * @see http://www.ietf.org/rfc/rfc7231.txt
 */
interface MessageInterface
{

	/**
	 * Sets the value of the specified header.
	 *
	 * While header names are case-insensitive, the casing of the header will
	 * be preserved by this function, and returned from getHeaders().
	 *
	 * @param string          $name  Case-insensitive header field name.
	 * @param string|string[] $value Header value(s).
	 *
	 * @return self
	 * @throws \InvalidArgumentException for invalid header names or values.
	 */
	public function setHeader(string $name, $value): self;

	//--------------------------------------------------------------------

	/**
	 * Adds a new value to an existing header.
	 *
	 * @param string $name  Case-insensitive header field name.
	 * @param        $value Header value.
	 *
	 * @return MessageInterface
	 */
	public function appendHeader(string $name, $value): self;

	//--------------------------------------------------------------------

	/**
	 * Removes a header.
	 *
	 * @param string $name Case-insensitive header field name to remove.
	 *
	 * @return MessageInterface
	 */
	public function removeHeader(string $name): self;

	//--------------------------------------------------------------------

	/**
	 * Checks if a header exists by the given case-insensitive name.
	 *
	 * @param string $name Case-insensitive header field name.
	 *
	 * @return bool Returns true if any header names match the given header
	 *     name using a case-insensitive string comparison. Returns false if
	 *     no matching header name is found in the message.
	 */
	public function hasHeader(string $name): bool;

	//--------------------------------------------------------------------

	/**
	 * Retrieves a message header value by the given case-insensitive name.
	 *
	 * This method returns an array of all the header values of the given
	 * case-insensitive header name.
	 *
	 * If the header does not appear in the message, this method MUST return an
	 * empty array.
	 *
	 * @param string $name Case-insensitive header field name.
	 *
	 * @return string[] An array of string values as provided for the given
	 *    header. If the header does not appear in the message, this method MUST
	 *    return an empty array.
	 */
	public function getHeader(string $name);

	//--------------------------------------------------------------------

	/**
	 * Retrieves all message header values.
	 *
	 * The keys represent the header name as it will be sent over the wire, and
	 * each value is an array of strings associated with the header.
	 *
	 *     // Represent the headers as a string
	 *     foreach ($message->getHeaders() as $name => $values) {
	 *         echo $name . ": " . implode(", ", $values);
	 *     }
	 *
	 *     // Emit headers iteratively:
	 *     foreach ($message->getHeaders() as $name => $values) {
	 *         foreach ($values as $value) {
	 *             header(sprintf('%s: %s', $name, $value), false);
	 *         }
	 *     }
	 *
	 * While header names are not case-sensitive, getHeaders() will preserve the
	 * exact case in which headers were originally specified.
	 *
	 * @return mixed
	 */
	public function getHeaders();

	//--------------------------------------------------------------------

	public function getStartLine();

	//--------------------------------------------------------------------

	/**
	 * Retrieves the HTTP protocol version as a string.
	 *
	 * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
	 *
	 * @return string HTTP protocol version.
	 */
	public function getProtocolVersion(): string;

	//--------------------------------------------------------------------

	/**
	 * Sets the message body.
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function setBody(&$data);

	//--------------------------------------------------------------------

	/**
	 * Gets the body of the message.
	 *
	 * @return mixed
	 */
	public function getBody();

	//--------------------------------------------------------------------

}
