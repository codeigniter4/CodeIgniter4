<?php namespace CodeIgniter\HTTPLite;

class URI {

	/**
	 * Retrieve the scheme component of the URI.
	 *
	 * If no scheme is present, this method MUST return an empty string.
	 *
	 * The value returned MUST be normalized to lowercase, per RFC 3986
	 * Section 3.1.
	 *
	 * The trailing ":" character is not part of the scheme and MUST NOT be
	 * added.
	 *
	 * @see https://tools.ietf.org/html/rfc3986#section-3.1
	 * @return string The URI scheme.
	 */
	public function scheme()
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the authority component of the URI.
	 *
	 * If no authority information is present, this method MUST return an empty
	 * string.
	 *
	 * The authority syntax of the URI is:
	 *
	 * <pre>
	 * [user-info@]host[:port]
	 * </pre>
	 *
	 * If the port component is not set or is the standard port for the current
	 * scheme, it SHOULD NOT be included.
	 *
	 * @see https://tools.ietf.org/html/rfc3986#section-3.2
	 * @return string The URI authority, in "[user-info@]host[:port]" format.
	 */
	public function authority()
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the user information component of the URI.
	 *
	 * If no user information is present, this method MUST return an empty
	 * string.
	 *
	 * If a user is present in the URI, this will return that value;
	 * additionally, if the password is also present, it will be appended to the
	 * user value, with a colon (":") separating the values.
	 *
	 * The trailing "@" character is not part of the user information and MUST
	 * NOT be added.
	 *
	 * @return string The URI user information, in "username[:password]" format.
	 */
	public function userInfo()
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the host component of the URI.
	 *
	 * If no host is present, this method MUST return an empty string.
	 *
	 * The value returned MUST be normalized to lowercase, per RFC 3986
	 * Section 3.2.2.
	 *
	 * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
	 * @return string The URI host.
	 */
	public function host()
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the port component of the URI.
	 *
	 * If a port is present, and it is non-standard for the current scheme,
	 * this method MUST return it as an integer. If the port is the standard port
	 * used with the current scheme, this method SHOULD return null.
	 *
	 * If no port is present, and no scheme is present, this method MUST return
	 * a null value.
	 *
	 * If no port is present, but a scheme is present, this method MAY return
	 * the standard port for that scheme, but SHOULD return null.
	 *
	 * @return null|int The URI port.
	 */
	public function port()
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the path component of the URI.
	 *
	 * The path can either be empty or absolute (starting with a slash) or
	 * rootless (not starting with a slash). Implementations MUST support all
	 * three syntaxes.
	 *
	 * Normally, the empty path "" and absolute path "/" are considered equal as
	 * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
	 * do this normalization because in contexts with a trimmed base path, e.g.
	 * the front controller, this difference becomes significant. It's the task
	 * of the user to handle both "" and "/".
	 *
	 * The value returned MUST be percent-encoded, but MUST NOT double-encode
	 * any characters. To determine what characters to encode, please refer to
	 * RFC 3986, Sections 2 and 3.3.
	 *
	 * As an example, if the value should include a slash ("/") not intended as
	 * delimiter between path segments, that value MUST be passed in encoded
	 * form (e.g., "%2F") to the instance.
	 *
	 * @see https://tools.ietf.org/html/rfc3986#section-2
	 * @see https://tools.ietf.org/html/rfc3986#section-3.3
	 * @return string The URI path.
	 */
	public function path()
	{

	}

	//--------------------------------------------------------------------

	public function segments()
	{

	}

	//--------------------------------------------------------------------

	public function segment(int $number)
	{

	}

	//--------------------------------------------------------------------

	public function totalSegments(): int
	{

	}

	//--------------------------------------------------------------------


}