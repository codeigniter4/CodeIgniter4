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
use InvalidArgumentException;

/**
 * Abstraction for a uniform resource identifier (URI).
 */
class URI
{
	/**
	 * Sub-delimiters used in query strings and fragments.
	 *
	 * @const string
	 */
	const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

	/**
	 * Unreserved characters used in paths, query strings, and fragments.
	 *
	 * @const string
	 */
	const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';

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
	 * @var string
	 */
	protected $scheme = 'http';

	/**
	 * URI User Info
	 *
	 * @var string
	 */
	protected $user;

	/**
	 * URI User Password
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * URI Host
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * URI Port
	 *
	 * @var integer
	 */
	protected $port;

	/**
	 * URI path.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * The name of any fragment.
	 *
	 * @var string
	 */
	protected $fragment = '';

	/**
	 * The query string.
	 *
	 * @var array
	 */
	protected $query = [];

	/**
	 * Default schemes/ports.
	 *
	 * @var array
	 */
	protected $defaultPorts = [
		'http'  => 80,
		'https' => 443,
		'ftp'   => 21,
		'sftp'  => 22,
	];

	/**
	 * Whether passwords should be shown in userInfo/authority calls.
	 * Default to false because URIs often show up in logs
	 *
	 * @var boolean
	 */
	protected $showPassword = false;

	/**
	 * If true, will continue instead of throwing exceptions.
	 *
	 * @var boolean
	 */
	protected $silent = false;

	/**
	 * If true, will use raw query string.
	 *
	 * @var boolean
	 */
	protected $rawQueryString = false;

	//--------------------------------------------------------------------

	/**
	 * Builds a representation of the string from the component parts.
	 *
	 * @param string $scheme
	 * @param string $authority
	 * @param string $path
	 * @param string $query
	 * @param string $fragment
	 *
	 * @return string
	 */
	public static function createURIString(string $scheme = null, string $authority = null, string $path = null, string $query = null, string $fragment = null): string
	{
		$uri = '';
		if (! empty($scheme))
		{
			$uri .= $scheme . '://';
		}

		if (! empty($authority))
		{
			$uri .= $authority;
		}

		if (isset($path) && $path !== '')
		{
			$uri .= substr($uri, -1, 1) !== '/' ? '/' . ltrim($path, '/') : ltrim($path, '/');
		}

		if ($query)
		{
			$uri .= '?' . $query;
		}

		if ($fragment)
		{
			$uri .= '#' . $fragment;
		}

		return $uri;
	}

	/**
	 * Used when resolving and merging paths to correctly interpret and
	 * remove single and double dot segments from the path per
	 * RFC 3986 Section 5.2.4
	 *
	 * @see http://tools.ietf.org/html/rfc3986#section-5.2.4
	 *
	 * @param string $path
	 *
	 * @return   string
	 * @internal
	 */
	public static function removeDotSegments(string $path): string
	{
		if ($path === '' || $path === '/')
		{
			return $path;
		}

		$output = [];

		$input = explode('/', $path);

		if ($input[0] === '')
		{
			unset($input[0]);
			$input = array_values($input);
		}

		// This is not a perfect representation of the
		// RFC, but matches most cases and is pretty
		// much what Guzzle uses. Should be good enough
		// for almost every real use case.
		foreach ($input as $segment)
		{
			if ($segment === '..')
			{
				array_pop($output);
			}
			elseif ($segment !== '.' && $segment !== '')
			{
				$output[] = $segment;
			}
		}

		$output = implode('/', $output);
		$output = trim($output, '/ ');

		// Add leading slash if necessary
		if (strpos($path, '/') === 0)
		{
			$output = '/' . $output;
		}

		// Add trailing slash if necessary
		if ($output !== '/' && substr($path, -1, 1) === '/')
		{
			$output .= '/';
		}

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param string $uri
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(string $uri = null)
	{
		if (! is_null($uri))
		{
			$this->setURI($uri);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * If $silent == true, then will not throw exceptions and will
	 * attempt to continue gracefully.
	 *
	 * @param boolean $silent
	 *
	 * @return URI
	 */
	public function setSilent(bool $silent = true)
	{
		$this->silent = $silent;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * If $raw == true, then will use parseStr() method
	 * instead of native parse_str() function.
	 *
	 * @param boolean $raw
	 *
	 * @return URI
	 */
	public function useRawQueryString(bool $raw = true)
	{
		$this->rawQueryString = $raw;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets and overwrites any current URI information.
	 *
	 * @param string|null $uri
	 *
	 * @return URI
	 */
	public function setURI(string $uri = null)
	{
		if (! is_null($uri))
		{
			$parts = parse_url($uri);

			if ($parts === false)
			{
				if ($this->silent)
				{
					return $this;
				}

				throw HTTPException::forUnableToParseURI($uri);
			}

			$this->applyParts($parts);
		}

		return $this;
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
	 * @see    https://tools.ietf.org/html/rfc3986#section-3.1
	 * @return string The URI scheme.
	 */
	public function getScheme(): string
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
	 *
	 * @param boolean $ignorePort
	 *
	 * @return string The URI authority, in "[user-info@]host[:port]" format.
	 */
	public function getAuthority(bool $ignorePort = false): string
	{
		if (empty($this->host))
		{
			return '';
		}

		$authority = $this->host;

		if (! empty($this->getUserInfo()))
		{
			$authority = $this->getUserInfo() . '@' . $authority;
		}

		// Don't add port if it's a standard port for
		// this scheme
		if (! empty($this->port) && ! $ignorePort && $this->port !== $this->defaultPorts[$this->scheme])
		{
			$authority .= ':' . $this->port;
		}

		$this->showPassword = false;

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
	 * NOTE that be default, the password, if available, will NOT be shown
	 * as a security measure as discussed in RFC 3986, Section 7.5. If you know
	 * the password is not a security issue, you can force it to be shown
	 * with $this->showPassword();
	 *
	 * The trailing "@" character is not part of the user information and MUST
	 * NOT be added.
	 *
	 * @return string|null The URI user information, in "username[:password]" format.
	 */
	public function getUserInfo()
	{
		$userInfo = $this->user;

		if ($this->showPassword === true && ! empty($this->password))
		{
			$userInfo .= ':' . $this->password;
		}

		return $userInfo;
	}

	//--------------------------------------------------------------------

	/**
	 * Temporarily sets the URI to show a password in userInfo. Will
	 * reset itself after the first call to authority().
	 *
	 * @param boolean $val
	 *
	 * @return URI
	 */
	public function showPassword(bool $val = true)
	{
		$this->showPassword = $val;

		return $this;
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
	 * @see    http://tools.ietf.org/html/rfc3986#section-3.2.2
	 * @return string The URI host.
	 */
	public function getHost(): string
	{
		return $this->host ?? '';
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
	 * @return null|integer The URI port.
	 */
	public function getPort()
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
	 * @see    https://tools.ietf.org/html/rfc3986#section-2
	 * @see    https://tools.ietf.org/html/rfc3986#section-3.3
	 * @return string The URI path.
	 */
	public function getPath(): string
	{
		return $this->path ?? '';
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the query string
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function getQuery(array $options = []): string
	{
		$vars = $this->query;

		if (array_key_exists('except', $options))
		{
			if (! is_array($options['except']))
			{
				$options['except'] = [$options['except']];
			}

			foreach ($options['except'] as $var)
			{
				unset($vars[$var]);
			}
		}
		elseif (array_key_exists('only', $options))
		{
			$temp = [];

			if (! is_array($options['only']))
			{
				$options['only'] = [$options['only']];
			}

			foreach ($options['only'] as $var)
			{
				if (array_key_exists($var, $vars))
				{
					$temp[$var] = $vars[$var];
				}
			}

			$vars = $temp;
		}

		return empty($vars) ? '' : http_build_query($vars);
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve a URI fragment
	 *
	 * @return string
	 */
	public function getFragment(): string
	{
		return $this->fragment ?? '';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the segments of the path as an array.
	 *
	 * @return array
	 */
	public function getSegments(): array
	{
		return $this->segments;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the value of a specific segment of the URI path.
	 *
	 * @param integer $number  Segment number
	 * @param string  $default Default value
	 *
	 * @return string     The value of the segment. If no segment is found,
	 *                    throws InvalidArgumentError
	 */
	public function getSegment(int $number, string $default = ''): string
	{
		// The segment should treat the array as 1-based for the user
		// but we still have to deal with a zero-based array.
		$number -= 1;

		if ($number > count($this->segments) && ! $this->silent)
		{
			throw HTTPException::forURISegmentOutOfRange($number);
		}

		return $this->segments[$number] ?? $default;
	}

	/**
	 * Set the value of a specific segment of the URI path.
	 * Allows to set only existing segments or add new one.
	 *
	 * @param integer $number
	 * @param mixed   $value  (string or int)
	 *
	 * @return $this
	 */
	public function setSegment(int $number, $value)
	{
		// The segment should treat the array as 1-based for the user
		// but we still have to deal with a zero-based array.
		$number -= 1;

		if ($number > count($this->segments) + 1)
		{
			if ($this->silent)
			{
				return $this;
			}

			throw HTTPException::forURISegmentOutOfRange($number);
		}

		$this->segments[$number] = $value;
		$this->refreshPath();

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the total number of segments.
	 *
	 * @return integer
	 */
	public function getTotalSegments(): int
	{
		return count($this->segments);
	}

	//--------------------------------------------------------------------

	/**
	 * Formats the URI as a string.
	 *
	 * Warning: For backwards-compatability this method
	 * assumes URIs with the same host as baseURL should
	 * be relative to the project's configuration.
	 * This aspect of __toString() is deprecated and should be avoided.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		$path   = $this->getPath();
		$scheme = $this->getScheme();

		// Check if this is an internal URI
		$config  = config('App');
		$baseUri = new self($config->baseURL);

		// If the hosts matches then assume this should be relative to baseURL
		if ($this->getHost() === $baseUri->getHost())
		{
			// Check for additional segments
			$basePath = trim($baseUri->getPath(), '/') . '/';
			$trimPath = ltrim($path, '/');

			if ($basePath !== '/' && strpos($trimPath, $basePath) !== 0)
			{
				$path = $basePath . $trimPath;
			}

			// Check for forced HTTPS
			if ($config->forceGlobalSecureRequests)
			{
				$scheme = 'https';
			}
		}

		return static::createURIString(
			$scheme, $this->getAuthority(), $path, // Absolute URIs should use a "/" for an empty path
			$this->getQuery(), $this->getFragment()
		);
	}

	//--------------------------------------------------------------------

	/**
	 * Parses the given string and saves the appropriate authority pieces.
	 *
	 * @param string $str
	 *
	 * @return $this
	 */
	public function setAuthority(string $str)
	{
		$parts = parse_url($str);

		if (! isset($parts['path']))
		{
			$parts['path'] = $this->getPath();
		}

		if (empty($parts['host']) && $parts['path'] !== '')
		{
			$parts['host'] = $parts['path'];
			unset($parts['path']); // @phpstan-ignore-line
		}

		$this->applyParts($parts);

		return $this;
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
	 * @param string $str
	 *
	 * @return $this
	 */
	public function setScheme(string $str)
	{
		$str = strtolower($str);
		$str = preg_replace('#:(//)?$#', '', $str);

		$this->scheme = $str;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the userInfo/Authority portion of the URI.
	 *
	 * @param string $user The user's username
	 * @param string $pass The user's password
	 *
	 * @return $this
	 */
	public function setUserInfo(string $user, string $pass)
	{
		$this->user     = trim($user);
		$this->password = trim($pass);

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
	 * @param integer $port
	 *
	 * @return $this
	 */
	public function setPort(int $port = null)
	{
		if (is_null($port))
		{
			return $this;
		}

		if ($port <= 0 || $port > 65535)
		{
			if ($this->silent)
			{
				return $this;
			}

			throw HTTPException::forInvalidPort($port);
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
		$this->path = $this->filterPath($path);

		$tempPath = trim($this->path, '/');

		$this->segments = ($tempPath === '') ? [] : explode('/', $tempPath);

		return $this;
	}

	/**
	 * Sets the path portion of the URI based on segments.
	 *
	 * @return $this
	 */
	public function refreshPath()
	{
		$this->path = $this->filterPath(implode('/', $this->segments));

		$tempPath = trim($this->path, '/');

		$this->segments = ($tempPath === '') ? [] : explode('/', $tempPath);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the query portion of the URI, while attempting
	 * to clean the various parts of the query keys and values.
	 *
	 * @param string $query
	 *
	 * @return $this
	 */
	public function setQuery(string $query)
	{
		if (strpos($query, '#') !== false)
		{
			if ($this->silent)
			{
				return $this;
			}

			throw HTTPException::forMalformedQueryString();
		}

		// Can't have leading ?
		if (! empty($query) && strpos($query, '?') === 0)
		{
			$query = substr($query, 1);
		}

		if ($this->rawQueryString)
		{
			$this->query = $this->parseStr($query);
		}
		else
		{
			parse_str($query, $this->query);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * A convenience method to pass an array of items in as the Query
	 * portion of the URI.
	 *
	 * @param array $query
	 *
	 * @return URI
	 */
	public function setQueryArray(array $query)
	{
		$query = http_build_query($query);

		return $this->setQuery($query);
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a single new element to the query vars.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function addQuery(string $key, $value = null)
	{
		$this->query[$key] = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Removes one or more query vars from the URI.
	 *
	 * @param string ...$params
	 *
	 * @return $this
	 */
	public function stripQuery(...$params)
	{
		foreach ($params as $param)
		{
			unset($this->query[$param]);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Filters the query variables so that only the keys passed in
	 * are kept. The rest are removed from the object.
	 *
	 * @param string ...$params
	 *
	 * @return $this
	 */
	public function keepQuery(...$params)
	{
		$temp = [];

		foreach ($this->query as $key => $value)
		{
			if (! in_array($key, $params, true))
			{
				continue;
			}

			$temp[$key] = $value;
		}

		$this->query = $temp;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the fragment portion of the URI.
	 *
	 * @see https://tools.ietf.org/html/rfc3986#section-3.5
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
	 * Encodes any dangerous characters, and removes dot segments.
	 * While dot segments have valid uses according to the spec,
	 * this URI class does not allow them.
	 *
	 * @param string|null $path
	 *
	 * @return string
	 */
	protected function filterPath(string $path = null): string
	{
		$orig = $path;

		// Decode/normalize percent-encoded chars so
		// we can always have matching for Routes, etc.
		$path = urldecode($path);

		// Remove dot segments
		$path = self::removeDotSegments($path);

		// Fix up some leading slash edge cases...
		if (strpos($orig, './') === 0)
		{
			$path = '/' . $path;
		}
		if (strpos($orig, '../') === 0)
		{
			$path = '/' . $path;
		}

		// Encode characters
		$path = preg_replace_callback(
				'/(?:[^' . static::CHAR_UNRESERVED . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/', function (array $matches) {
					return rawurlencode($matches[0]);
				}, $path
		);

		return $path;
	}

	//--------------------------------------------------------------------

	/**
	 * Saves our parts from a parse_url call.
	 *
	 * @param array $parts
	 */
	protected function applyParts(array $parts)
	{
		if (! empty($parts['host']))
		{
			$this->host = $parts['host'];
		}
		if (! empty($parts['user']))
		{
			$this->user = $parts['user'];
		}
		if (isset($parts['path']) && $parts['path'] !== '')
		{
			$this->path = $this->filterPath($parts['path']);
		}
		if (! empty($parts['query']))
		{
			$this->setQuery($parts['query']);
		}
		if (! empty($parts['fragment']))
		{
			$this->fragment = $parts['fragment'];
		}

		// Scheme
		if (isset($parts['scheme']))
		{
			$this->setScheme(rtrim($parts['scheme'], ':/'));
		}
		else
		{
			$this->setScheme('http');
		}

		// Port
		if (isset($parts['port']) && ! is_null($parts['port']))
		{
			// Valid port numbers are enforced by earlier parse_url or setPort()
			$port       = $parts['port'];
			$this->port = $port;
		}

		if (isset($parts['pass']))
		{
			$this->password = $parts['pass'];
		}

		// Populate our segments array
		if (isset($parts['path']) && $parts['path'] !== '')
		{
			$tempPath = trim($parts['path'], '/');

			$this->segments = ($tempPath === '') ? [] : explode('/', $tempPath);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Combines one URI string with this one based on the rules set out in
	 * RFC 3986 Section 2
	 *
	 * @see http://tools.ietf.org/html/rfc3986#section-5.2
	 *
	 * @param string $uri
	 *
	 * @return URI
	 */
	public function resolveRelativeURI(string $uri)
	{
		/*
		 * NOTE: We don't use removeDotSegments in this
		 * algorithm since it's already done by this line!
		 */
		$relative = new self();
		$relative->setURI($uri);

		if ($relative->getScheme() === $this->getScheme())
		{
			$relative->setScheme('');
		}

		$transformed = clone $relative;

		// 5.2.2 Transform References in a non-strict method (no scheme)
		if (! empty($relative->getAuthority()))
		{
			$transformed->setAuthority($relative->getAuthority())
					->setPath($relative->getPath())
					->setQuery($relative->getQuery());
		}
		else
		{
			if ($relative->getPath() === '')
			{
				$transformed->setPath($this->getPath());

				if ($relative->getQuery())
				{
					$transformed->setQuery($relative->getQuery());
				}
				else
				{
					$transformed->setQuery($this->getQuery());
				}
			}
			else
			{
				if (strpos($relative->getPath(), '/') === 0)
				{
					$transformed->setPath($relative->getPath());
				}
				else
				{
					$transformed->setPath($this->mergePaths($this, $relative));
				}

				$transformed->setQuery($relative->getQuery());
			}

			$transformed->setAuthority($this->getAuthority());
		}

		$transformed->setScheme($this->getScheme());

		$transformed->setFragment($relative->getFragment());

		return $transformed;
	}

	//--------------------------------------------------------------------

	/**
	 * Given 2 paths, will merge them according to rules set out in RFC 2986,
	 * Section 5.2
	 *
	 * @see http://tools.ietf.org/html/rfc3986#section-5.2.3
	 *
	 * @param URI $base
	 * @param URI $reference
	 *
	 * @return string
	 */
	protected function mergePaths(URI $base, URI $reference): string
	{
		if (! empty($base->getAuthority()) && $base->getPath() === '')
		{
			return '/' . ltrim($reference->getPath(), '/ ');
		}

		$path = explode('/', $base->getPath());

		if ($path[0] === '')
		{
			unset($path[0]);
		}

		array_pop($path);
		$path[] = $reference->getPath();

		return implode('/', $path);
	}

	//--------------------------------------------------------------------

	/**
	 * This is equivalent to the native PHP parse_str() function.
	 * This version allows the dot to be used as a key of the query string.
	 *
	 * @param string $query
	 *
	 * @return array
	 */
	protected function parseStr(string $query): array
	{
		$return = [];
		$query  = explode('&', $query);

		$params = array_map(function (string $chunk) {
			return preg_replace_callback('/^(?<key>[^&=]+?)(?:\[[^&=]*\])?=(?<value>[^&=]+)/', function (array $match) {
				return str_replace($match['key'], bin2hex($match['key']), $match[0]);
			}, urldecode($chunk));
		}, $query);

		$params = implode('&', $params);
		parse_str($params, $params);

		foreach ($params as $key => $value)
		{
			$return[hex2bin($key)] = $value;
		}

		$query = $params = null;

		return $return;
	}

	//--------------------------------------------------------------------
}
