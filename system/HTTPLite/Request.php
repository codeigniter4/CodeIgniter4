<?php namespace CodeIgniter\HTTPLite;

class Request
{
	/**
	 * IP address of the current user.
	 *
	 * @var string
	 */
	protected $ipAddress;

	/**
	 * List of all HTTP request headers
	 *
	 * @var array
	 */
	protected $headers = [];


	protected $inputStream;

	/**
	 * Enable CSRF flag
	 *
	 * Enables a CSRF cookie token to be set.
	 * Set automatically based on config setting.
	 *
	 * @var bool
	 */
	protected $enableCSRF = false;

	//--------------------------------------------------------------------

	public function __construct()
	{
	    // @todo get values from configuration

		// @todo perform csrf check
	}

	//--------------------------------------------------------------------


	/**
	 * Determines if this request was made from the command line (CLI).
	 *
	 * @return bool
	 */
	public function isCLI(): bool
	{
		return (PHP_SAPI === 'cli' OR defined('STDIN'));
	}

	//--------------------------------------------------------------------

	/**
	 * Test to see if a request contains the HTTP_X_REQUESTED_WITH header.
	 *
	 * @return bool
	 */
	public function isAJAX(): bool
	{
		return ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the user's IP address.
	 *
	 * @return string IP address
	 */
	public function ipAddress(): string
	{
		if ($this->ipAddress !== FALSE)
		{
			return $this->ipAddress;
		}

		$proxy_ips = config_item('proxy_ips');
		if ( ! empty($proxy_ips) && ! is_array($proxy_ips))
		{
			$proxy_ips = explode(',', str_replace(' ', '', $proxy_ips));
		}

		$this->ip_address = $this->server('REMOTE_ADDR');

		if ($proxy_ips)
		{
			foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP') as $header)
			{
				if (($spoof = $this->server($header)) !== NULL)
				{
					// Some proxies typically list the whole chain of IP
					// addresses through which the client has reached us.
					// e.g. client_ip, proxy_ip1, proxy_ip2, etc.
					sscanf($spoof, '%[^,]', $spoof);

					if ( ! $this->validIP($spoof))
					{
						$spoof = NULL;
					}
					else
					{
						break;
					}
				}
			}

			if ($spoof)
			{
				for ($i = 0, $c = count($proxy_ips); $i < $c; $i++)
				{
					// Check if we have an IP address or a subnet
					if (strpos($proxy_ips[$i], '/') === FALSE)
					{
						// An IP address (and not a subnet) is specified.
						// We can compare right away.
						if ($proxy_ips[$i] === $this->ipAddress)
						{
							$this->ipAddress = $spoof;
							break;
						}

						continue;
					}

					// We have a subnet ... now the heavy lifting begins
					isset($separator) OR $separator = $this->validIP($this->ipAddress, 'ipv6') ? ':' : '.';

					// If the proxy entry doesn't match the IP protocol - skip it
					if (strpos($proxy_ips[$i], $separator) === FALSE)
					{
						continue;
					}

					// Convert the REMOTE_ADDR IP address to binary, if needed
					if ( ! isset($ip, $sprintf))
					{
						if ($separator === ':')
						{
							// Make sure we're have the "full" IPv6 format
							$ip = explode(':',
								str_replace('::',
									str_repeat(':', 9 - substr_count($this->ipAddress, ':')),
									$this->ipAddress
								)
							);

							for ($j = 0; $j < 8; $j++)
							{
								$ip[$j] = intval($ip[$j], 16);
							}

							$sprintf = '%016b%016b%016b%016b%016b%016b%016b%016b';
						}
						else
						{
							$ip = explode('.', $this->ipAddress);
							$sprintf = '%08b%08b%08b%08b';
						}

						$ip = vsprintf($sprintf, $ip);
					}

					// Split the netmask length off the network address
					sscanf($proxy_ips[$i], '%[^/]/%d', $netaddr, $masklen);

					// Again, an IPv6 address is most likely in a compressed form
					if ($separator === ':')
					{
						$netaddr = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($netaddr, ':')), $netaddr));
						for ($i = 0; $i < 8; $i++)
						{
							$netaddr[$i] = intval($netaddr[$i], 16);
						}
					}
					else
					{
						$netaddr = explode('.', $netaddr);
					}

					// Convert to binary and finally compare
					if (strncmp($ip, vsprintf($sprintf, $netaddr), $masklen) === 0)
					{
						$this->ip_address = $spoof;
						break;
					}
				}
			}
		}

		if ( ! $this->validIP($this->ipAddress))
		{
			return $this->ipAddress = '0.0.0.0';
		}

		return $this->ipAddress;
	}

	//--------------------------------------------------------------------

	/**
	 * Validate an IP address
	 *
	 * @param        $ip     IP Address
	 * @param string $which  IP protocol: 'ipv4' or 'ipv6'
	 *
	 * @return bool
	 */
	public function validIP($ip, string $which = ''): bool
	{
		switch (strtolower($which))
		{
			case 'ipv4':
				$which = FILTER_FLAG_IPV4;
				break;
			case 'ipv6':
				$which = FILTER_FLAG_IPV6;
				break;
			default:
				$which = NULL;
				break;
		}

		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $which);
	}

	//--------------------------------------------------------------------


	/**
	 * Get the request method.
	 *
	 * @param bool|false $upper Whether to return in upper or lower case.
	 *
	 * @return string
	 */
	public function method($upper = false): string
	{
		return ($upper)
			? strtoupper($this->server('REQUEST_METHOD'))
			: strtolower($this->server('REQUEST_METHOD'));
	}

	//--------------------------------------------------------------------


	/**
	 * Fetch an item from GET data.
	 *
	 * @param null $index   Index for item to fetch from $_GET.
	 * @param null $filter  A filter name to apply.
	 * @return mixed
	 */
	public function get($index = null, $filter = null)
	{
		return $this->fetchGlobal(INPUT_GET, $index, $filter);
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from POST.
	 *
	 * @param null $index   Index for item to fetch from $_POST.
	 * @param null $filter  A filter name to apply
	 * @return mixed
	 */
	public function post($index = null, $filter = null)
	{
		return $this->fetchGlobal(INPUT_POST, $index, $filter);
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from POST data with fallback to GET.
	 *
	 * @param null $index   Index for item to fetch from $_POST or $_GET
	 * @param null $filter  A filter name to apply
	 * @return mixed
	 */
	public function postGet($index = null, $filter = null)
	{
		// Use $_POST directly here, since filter_has_var only
		// checks the initial POST data, not anything that might
		// have been added since.
		return isset($_POST[$index])
			? $this->post($index, $filter)
			: $this->get($index, $filter);
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from GET data with fallback to POST.
	 *
	 * @param null $index   Index for item to be fetched from $_GET or $_POST
	 * @param null $filter  A filter name to apply
	 * @return mixed
	 */
	public function getPost($index = null, $filter = null)
	{
		// Use $_GET directly here, since filter_has_var only
		// checks the initial GET data, not anything that might
		// have been added since.
		return isset($_GET[$index])
			? $this->get($index, $filter)
			: $this->post($index, $filter);
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from the COOKIE array.
	 *
	 * @param null $index   Index for item to be fetched from $_COOKIE
	 * @param null $filter  A filter name to be applied
	 * @return mixed
	 */
	public function cookie($index = null, $filter = null)
	{
		return $this->fetchGlobal(INPUT_COOKIE, $index, $filter);
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from the $_SERVER array.
	 *
	 * @param null $index   Index for item to be fetched from $_SERVER
	 * @param null $filter  A filter name to be applied
	 * @return mixed
	 */
	public function server($index = null, $filter = null)
	{
		return $this->fetchGlobal(INPUT_SERVER, $index, $filter);
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an iterm from the php://input stream
	 *
	 * Useful when you need to access PUT, DELETE or PATCH request data.
	 *
	 * @param null $index   Index for item to be fetched
	 * @param null $filter  A filter to apply
	 * @return mixed
	 */
	public function inputStream($index = null, $filter = null)
	{
	}

	//--------------------------------------------------------------------

	/**
	 * Set a cookie
	 *
	 * Accepts an arbitrary number of parameters (up to 7) or an associateive
	 * array in the first parameter containing all the values.
	 *
	 * @param            $name      Cookie name or array containing parameters
	 * @param string     $value     Cookie value
	 * @param string     $expire    Cookie expiration time in seconds
	 * @param string     $domain    Cookie domain (e.g.: '.yourdomain.com')
	 * @param string     $path      Cookie path (default: '/')
	 * @param string     $prefix    Cookie name prefix
	 * @param bool|false $secure    Whether to only transfer cookies via SSL
	 * @param bool|false $httponly  Whether only make the cookie accessible via HTTP (no javascript)
	 */
	public function setCookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = FALSE, $httponly = FALSE)
	{
		if (is_array($name))
		{
			// always leave 'name' in last place, as the loop will break otherwise, due to $$item
			foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name') as $item)
			{
				if (isset($name[$item]))
				{
					$$item = $name[$item];
				}
			}
		}

		if ($prefix === '' && config_item('cookie_prefix') !== '')
		{
			$prefix = config_item('cookie_prefix');
		}

		if ($domain == '' && config_item('cookie_domain') != '')
		{
			$domain = config_item('cookie_domain');
		}

		if ($path === '/' && config_item('cookie_path') !== '/')
		{
			$path = config_item('cookie_path');
		}

		if ($secure === FALSE && config_item('cookie_secure') === TRUE)
		{
			$secure = config_item('cookie_secure');
		}

		if ($httponly === FALSE && config_item('cookie_httponly') !== FALSE)
		{
			$httponly = config_item('cookie_httponly');
		}

		if ( ! is_numeric($expire))
		{
			$expire = time() - 86500;
		}
		else
		{
			$expire = ($expire > 0) ? time() + $expire : 0;
		}

		setcookie($prefix.$name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch the user agent string
	 *
	 * @param null $filter
	 */
	public function userAgent($filter = null)
	{
		return $this->fetchGlobal(INPUT_SERVER, 'HTTP_USER_AGENT', $filter);
	}

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

				$this->headers[$header] = $this->fetchGlobal($_SERVER, $key, $filter);
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

		return filter_var($headers[$index], $filter);
	}

	//--------------------------------------------------------------------



	/**
	 * Fetches one or more items from a global, like cookies, get, post, etc.
	 * Can optionally filter the input when you retrieve it by passing in
	 * a filter.
	 *
	 * If $type is an array, it must conform to the input allowed by the
	 * filter_input_array method.
	 *
	 * http://php.net/manual/en/filter.filters.sanitize.php
	 *
	 * @param      $type
	 * @param null $index
	 * @param null $filter
	 *
	 * @return mixed
	 */
	protected function fetchGlobal($type, $index = null, $filter = null)
	{
		// If $index is null, it means that the whole input type array is requested
		if (is_null($index))
		{
			return filter_input_array($type, is_null($filter) ? FILTER_FLAG_NONE : $filter);
		}

		// allow fetching multiple keys at once
		if (is_array($index))
		{
			$output = [];

			foreach ($index as $key)
			{
				$output[$key] = filter_input($type, $key, $filter);
			}

			return $output;
		}
//
//		// Does the index contain array notation?
//		if (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1) // Does the index contain array notation
//		{
//			$value = $array;
//			for ($i = 0; $i < $count; $i++)
//			{
//				$key = trim($matches[0][$i], '[]');
//				if ($key === '') // Empty notation will return the value as array
//				{
//					break;
//				}
//
//				if (isset($value[$key]))
//				{
//					$value = $value[$key];
//				}
//				else
//				{
//					return NULL;
//				}
//			}
//		}

		// Single key to retrieve
		return filter_input($type, $index, $filter);
	}

	//--------------------------------------------------------------------

}