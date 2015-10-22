<?php namespace CodeIgniter\HTTPLite;

use App\Config\AppConfig;

class URI
{

	/**
	 * Current URI string
	 *
	 * @var string
	 */
	protected $uriString;

	/**
	 * List of URI segments.
	 *
	 * Starts at 1 instead of 0
	 *
	 * @var array
	 */
	protected $segments = [];

	/**
	 * The URI Scheme.
	 *
	 * @var
	 */
	protected $scheme;

	/**
	 * URI User Info
	 *
	 * @var
	 */
	protected $userInfo;

	/**
	 * URI Host
	 *
	 * @var
	 */
	protected $host;

	/**
	 * URI Port
	 *
	 * @var
	 */
	protected $port;

	/**
	 * URI path.
	 *
	 * @var
	 */
	protected $path;

	/**
	 * The name of any fragment.
	 *
	 * @var
	 */
	protected $fragment = '';

	/**
	 * Permitted URI chars
	 *
	 * PCRE character group allowed in URI segments.
	 *
	 * @var
	 */
	protected $permittedURIChars;

	/**
	 * Holds the app config variables.
	 *
	 * @var
	 */
	protected $uriProtocol;

	//--------------------------------------------------------------------

	public function __construct(string $uri = null)
	{
//		$this->uriProtocol = $config->uriProtocol;
//		$this->permittedURIChars = $config->permittedURIChars;

		if (! is_null($uri))
		{
			$parts = parse_url($uri);

			if ($parts === false)
			{
				throw new \InvalidArgumentException("Unable to parse URI: {$uri}");
			}

			$this->applyParts($parts);
		}
	}

	//--------------------------------------------------------------------

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
	public function scheme(): string
	{
		return $this->scheme;
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
	public function authority(): string
	{
		if (empty($this->host))
		{
			return '';
		}

		$authority = $this->host;

		if ( ! empty($this->userInfo))
		{
			$authority = $this->userInfo.'@'.$authority;
		}

		if ( ! empty($this->port))
		{
			$authority .= ':'.$this->port;
		}

		return $authority;
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
		return $this->userInfo;
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
		return $this->host;
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
		return $this->port;
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
	public function path(): string
	{
		return (is_null($this->path)) ? '' : $this->path;
	}

	//--------------------------------------------------------------------

	public function query(): string
	{
		return is_null($this->query) ? '' : $this->query;
	}

	//--------------------------------------------------------------------

	public function fragment(): string
	{
		return is_null($this->fragment) ? '' : $this->fragment;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the segments of the path as an array.
	 *
	 * @return array
	 */
	public function segments(): array
	{
		return $this->segments;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the value of a specific segment of the URI path.
	 *
	 * @param int $number
	 *
	 * @return string     The value of the segment. If no segment is found,
	 *                    throws InvalidArgumentError
	 */
	public function segment(int $number): string
	{
		// The segment should treat the array as 1-based for the user
		// but we still have to deal with a zero-based array.
		$number -= 1;

		if ($number > count($this->segments))
		{
			throw new \InvalidArgumentException('Request URI segment is our of range.');
		}

		return $this->segments[$number];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the total number of segments.
	 *
	 * @return int
	 */
	public function totalSegments(): int
	{
		return count($this->segments);
	}

	//--------------------------------------------------------------------

	/**
	 * Allow the URI to be output as a string by simply casting it to a string
	 * or echoing out.
	 */
	public function __toString(): string
	{
		return self::createURIString(
			$this->scheme(),
			$this->authority(),
			$this->host(),
			$this->path(), // Absolute URIs should use a "/" for an empty path
			$this->query(),
			$this->fragment()
		);
	}

	//--------------------------------------------------------------------

	/**
	 * Builds a representation of the string from the component parts.
	 *
	 * @param $scheme
	 * @param $authority
	 * @param $path
	 * @param $query
	 * @param $fragment
	 *
	 * @return string
	 */
	public static function createURIString($scheme, $authority, $host, $path, $query, $fragment)
	{
		$uri = '';
		if ( ! empty($scheme))
		{
			$uri .= $scheme.'://';
		}

		if ( ! empty($authority))
		{
			$uri .= $authority;
		}

		if (! empty($host))
		{
			$uri .= $host;
		}

		if ($path)
		{
			if (empty($path) || '/' !== substr($path, 0, 1))
			{
				$path = '/'.$path;
			}
			$uri .= $path;
		}

		if ($query)
		{
			$uri .= '?'.$query;
		}

		if ($fragment)
		{
			$uri .= '#'. $fragment;
		}

		return $uri;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the scheme for this URI.
	 *
	 * Because of the large number of valid schemes we cannot limit this
	 * to only http or https.
	 *
	 * @see https://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml
	 *
	 * @param $str
	 *
	 * @return $this
	 */
	public function setScheme(string $str)
	{
	    $this->scheme = strtolower($str);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the userInfo/Authority portion of the URI.
	 *
	 * @param string $user  The user's username
	 * @param string $pass  The user's password
	 *
	 * @return $this
	 */
	public function setUserInfo(string $user, string $pass)
	{
		$this->userInfo = trim($user).':'.trim($pass);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the host name to use.
	 *
	 * @param string $str
	 *
	 * @return $this
	 */
	public function setHost(string $str)
	{
		$this->host = trim($str);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the port portion of the URI.
	 *
	 * @param int $port
	 *
	 * @return $this
	 */
	public function setPort(int $port)
	{
	    if ($port < 0 || $port > 65535)
	    {
		    throw new \InvalidArgumentException('Invalid port given.');
	    }

		$this->port = $port;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the path portion of the URI.
	 *
	 * @param string $path
	 *
	 * @return $this
	 */
	public function setPath(string $path)
	{
	    $this->path = trim($path);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the fragment portion of the URI.
	 *
	 * @param string $string
	 *
	 * @return $this
	 */
	public function setFragment(string $string)
	{
	    $this->fragment = trim($string, '# ');

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Saves our parts from a parse_url call.
	 *
	 * @param $parts
	 */
	protected function applyParts($parts)
	{
		$this->host     = isset($parts['host']) ? $parts['host'] : '';
		$this->userInfo = isset($parts['user']) ? $parts['user'] : '';
		$this->path     = isset($parts['path']) ? $this->filterURI($parts['path']) : '';
		$this->query    = isset($parts['query']) ? $this->filterURI($parts['query']) : '';
		$this->fragment = isset($parts['fragment']) ? $this->filterURI($parts['fragment']) : '';

		// Scheme
		if (isset($parts['scheme']))
		{
			$this->scheme = rtrim(strtolower($parts['scheme']), ':/');
		}

		// Port
		if (isset($parts['port']))
		{
			if ( ! is_null($parts['port']))
			{
				$port = (int)$parts['port'];

				if (1 > $port || 0xffff < $port)
				{
					throw new \InvalidArgumentException('Ports must be between 1 and 65535');
				}

				$this->port = $port;
			}
		}

		if (isset($parts['pass']))
		{
			$this->userInfo .= ':'.$parts['pass'];
		}

		// Populate our segments array
		if ( ! empty($parts['path']))
		{
			$this->segments = explode('/', trim($parts['path'], '/'));
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Filters segments for malicious characters
	 *
	 * @param $str
	 */
	protected function filterURI(&$str)
	{
		if ( ! empty($str) && ! empty($this->_permittedURIChars) &&
		     ! preg_match('/^['.$this->permittedURIChars.']+$/i'.(UTF8_ENABLED ? 'u' : ''), $str)
		)
		{
			throw new \InvalidArgumentException('The URI you submitted has disallowed characters.', 400);
		}

		return $str;
	}

	//--------------------------------------------------------------------

}
