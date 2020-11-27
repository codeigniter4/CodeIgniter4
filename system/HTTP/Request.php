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

use Psr\Http\Message\UriInterface;

/**
 * Representation of an HTTP request.
 */
class Request extends Message implements RequestInterface
{
	/**
	 * IP address of the current user.
	 *
	 * @var string
	 */
	protected $ipAddress = '';

	/**
	 * Proxy IPs
	 *
	 * @var string|array
	 */
	protected $proxyIPs;

	/**
	 * Request method.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * A URI instance.
	 *
	 * @var URI
	 */
	protected $uri;

	/**
	 * Stores values we've retrieved from
	 * PHP globals.
	 *
	 * @var array
	 */
	protected $globals = [];

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param object $config
	 */
	public function __construct($config)
	{
		$this->proxyIPs = $config->proxyIPs;

		if (empty($this->method))
		{
			$this->method = $this->getServer('REQUEST_METHOD') ?? 'GET';
		}

		if (empty($this->uri))
		{
			$this->uri = new URI();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the user's IP address.
	 *
	 * @return string IP address
	 */
	public function getIPAddress(): string
	{
		if (! empty($this->ipAddress))
		{
			return $this->ipAddress;
		}

		$proxyIps = $this->proxyIPs;
		if (! empty($this->proxyIPs) && ! is_array($this->proxyIPs))
		{
			$proxyIps = explode(',', str_replace(' ', '', $this->proxyIPs));
		}

		$this->ipAddress = $this->getServer('REMOTE_ADDR');

		if ($proxyIps)
		{
			foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP'] as $header)
			{
				if (($spoof = $this->getServer($header)) !== null)
				{
					// Some proxies typically list the whole chain of IP
					// addresses through which the client has reached us.
					// e.g. client_ip, proxy_ip1, proxy_ip2, etc.
					sscanf($spoof, '%[^,]', $spoof);

					if (! $this->isValidIP($spoof))
					{
						$spoof = null;
					}
					else
					{
						break;
					}
				}
			}

			if ($spoof)
			{
				foreach ($proxyIps as $proxyIp)
				{
					// Check if we have an IP address or a subnet
					if (strpos($proxyIp, '/') === false)
					{
						// An IP address (and not a subnet) is specified.
						// We can compare right away.
						if ($proxyIp === $this->ipAddress)
						{
							$this->ipAddress = $spoof;
							break;
						}

						continue;
					}

					// We have a subnet ... now the heavy lifting begins
					// // @phpstan-ignore-next-line
					isset($separator) || $separator = $this->isValidIP($this->ipAddress, 'ipv6') ? ':' : '.';

					// If the proxy entry doesn't match the IP protocol - skip it
					if (strpos($proxyIp, $separator) === false) // @phpstan-ignore-line
					{
						continue;
					}

					// Convert the REMOTE_ADDR IP address to binary, if needed
					if (! isset($ip, $sprintf))
					{
						if ($separator === ':') // @phpstan-ignore-line
						{
							// Make sure we're have the "full" IPv6 format
							$ip = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($this->ipAddress, ':')), $this->ipAddress
									)
							);

							for ($j = 0; $j < 8; $j ++)
							{
								$ip[$j] = intval($ip[$j], 16);
							}

							$sprintf = '%016b%016b%016b%016b%016b%016b%016b%016b';
						}
						else
						{
							$ip      = explode('.', $this->ipAddress);
							$sprintf = '%08b%08b%08b%08b';
						}

						$ip = vsprintf($sprintf, $ip);
					}

					// Split the netmask length off the network address
					sscanf($proxyIp, '%[^/]/%d', $netaddr, $masklen);

					// Again, an IPv6 address is most likely in a compressed form
					if ($separator === ':') // @phpstan-ignore-line
					{
						$netaddr = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($netaddr, ':')), $netaddr));
						for ($i = 0; $i < 8; $i ++)
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
						$this->ipAddress = $spoof;
						break;
					}
				}
			}
		}

		if (! $this->isValidIP($this->ipAddress))
		{
			return $this->ipAddress = '0.0.0.0';
		}

		return empty($this->ipAddress) ? '' : $this->ipAddress;
	}

	//--------------------------------------------------------------------

	/**
	 * Validate an IP address
	 *
	 * @param string $ip    IP Address
	 * @param string $which IP protocol: 'ipv4' or 'ipv6'
	 *
	 * @return boolean
	 */
	public function isValidIP(string $ip = null, string $which = null): bool
	{
		switch (strtolower((string) $which))
		{
			case 'ipv4':
				$which = FILTER_FLAG_IPV4;
				break;
			case 'ipv6':
				$which = FILTER_FLAG_IPV6;
				break;
			default:
				$which = null;
				break;
		}

		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $which);
	}

	//--------------------------------------------------------------------

	/**
	 * Get the request method.
	 *
	 * @param boolean $upper Whether to return in upper or lower case.
	 *
	 * @return string
	 */
	public function getMethod(bool $upper = false): string
	{
		return ($upper) ? strtoupper($this->method) : strtolower($this->method);
	}

	/**
	 * Sets the request method. Used when spoofing the request.
	 *
	 * @param string $method
	 *
	 * @return Request
	 */
	public function setMethod(string $method)
	{
		$this->method = $method;

		return $this;
	}

	/**
	 * Returns an instance with the specified method.
	 *
	 * @param string $method
	 *
	 * @return self
	 */
	public function withMethod($method)
	{
		$clone = clone $this;

		return $clone->setMethod($method);
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves the message's request target.
	 *
	 * Retrieves the message's request-target either as it will appear (for
	 * clients), as it appeared at request (for servers), or as it was
	 * specified for the instance (see withRequestTarget()).
	 *
	 * In most cases, this will be the origin-form of the composed URI,
	 * unless a value was provided to the concrete implementation (see
	 * withRequestTarget() below).
	 *
	 * If no URI is available, and no request-target has been specifically
	 * provided, this method MUST return the string "/".
	 *
	 * @return string
	 */
	public function getRequestTarget()
	{
		return $this->uri === null ? '/' : (string) $this->uri;
	}

	/**
	 * Return an instance with the specific request-target.
	 *
	 * If the request needs a non-origin-form request-target — e.g., for
	 * specifying an absolute-form, authority-form, or asterisk-form —
	 * this method may be used to create an instance with the specified
	 * request-target, verbatim.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * changed request target.
	 *
	 * @link http://tools.ietf.org/html/rfc7230#section-2.7
	 * (for the various request-target forms allowed in request messages)
	 *
	 * @param string $requestTarget
	 *
	 * @return static
	 */
	public function withRequestTarget($requestTarget): self
	{
		$clone = clone $this;
		$clone->setUri(new URI($requestTarget));

		return $clone;
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from the $_SERVER array.
	 *
	 * @param string|array|null $index  Index for item to be fetched from $_SERVER
	 * @param integer|null      $filter A filter name to be applied
	 * @param null              $flags
	 *
	 * @return mixed
	 */
	public function getServer($index = null, $filter = null, $flags = null)
	{
		return $this->fetchGlobal('server', $index, $filter, $flags);
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves the URI instance.
	 *
	 * This method MUST return a UriInterface instance.
	 *
	 * @link http://tools.ietf.org/html/rfc3986#section-4.3
	 *
	 * @return UriInterface
	 */
	public function getUri()
	{
		return $this->uri ?? new URI();
	}

	/**
	 * Sets the URI instance.
	 *
	 * @param UriInterface $uri
	 *
	 * @return self
	 */
	public function setUri(UriInterface $uri): self
	{
		$this->uri = $uri; // @phpstan-ignore-line

		return $this;
	}

	/**
	 * Returns an instance with the provided URI.
	 *
	 * This method MUST update the Host header of the returned request by
	 * default if the URI contains a host component. If the URI does not
	 * contain a host component, any pre-existing Host header MUST be carried
	 * over to the returned request.
	 *
	 * You can opt-in to preserving the original state of the Host header by
	 * setting `$preserveHost` to `true`. When `$preserveHost` is set to
	 * `true`, this method interacts with the Host header in the following ways:
	 *
	 * - If the Host header is missing or empty, and the new URI contains
	 *   a host component, this method MUST update the Host header in the returned
	 *   request.
	 * - If the Host header is missing or empty, and the new URI does not contain a
	 *   host component, this method MUST NOT update the Host header in the returned
	 *   request.
	 * - If a Host header is present and non-empty, this method MUST NOT update
	 *   the Host header in the returned request.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * new UriInterface instance.
	 *
	 * @link http://tools.ietf.org/html/rfc3986#section-4.3
	 * @param UriInterface $uri New request URI to use.
	 * @param bool $preserveHost Preserve the original state of the Host header.
	 * @return static
	 */
	public function withUri(UriInterface $uri, $preserveHost = false)
	{
		$clone = clone $this;
		$clone->setUri($uri);

		if (! $preserveHost)
		{
			if ($uri->getHost())
			{
				$clone->setHeader('Host', $uri->getHost());
			}
		}
		elseif ($this->getHeaderLine('Host') === '' && $uri->getHost())
		{
			$clone->setHeader('Host', $uri->getHost());
		}

		return $clone;
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from the $_ENV array.
	 *
	 * @param null $index  Index for item to be fetched from $_ENV
	 * @param null $filter A filter name to be applied
	 * @param null $flags
	 *
	 * @return mixed
	 */
	public function getEnv($index = null, $filter = null, $flags = null)
	{
		return $this->fetchGlobal('env', $index, $filter, $flags);
	}

	//--------------------------------------------------------------------

	/**
	 * Allows manually setting the value of PHP global, like $_GET, $_POST, etc.
	 *
	 * @param string $method
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function setGlobal(string $method, $value)
	{
		$this->globals[$method] = $value;

		return $this;
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
	 * @param string            $method Input filter constant
	 * @param string|array|null $index
	 * @param integer|null      $filter Filter constant
	 * @param mixed             $flags
	 *
	 * @return mixed
	 */
	public function fetchGlobal($method, $index = null, $filter = null, $flags = null)
	{
		$method = strtolower($method);

		if (! isset($this->globals[$method]))
		{
			$this->populateGlobals($method);
		}

		// Null filters cause null values to return.
		if (is_null($filter))
		{
			$filter = FILTER_DEFAULT;
		}

		// Return all values when $index is null
		if (is_null($index))
		{
			$values = [];
			foreach ($this->globals[$method] as $key => $value)
			{
				$values[$key] = is_array($value)
					? $this->fetchGlobal($method, $key, $filter, $flags)
					: filter_var($value, $filter, $flags);
			}

			return $values;
		}

		// allow fetching multiple keys at once
		if (is_array($index))
		{
			$output = [];

			foreach ($index as $key)
			{
				$output[$key] = $this->fetchGlobal($method, $key, $filter, $flags);
			}

			return $output;
		}

		// Does the index contain array notation?
		if (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1)
		{
			$value = $this->globals[$method];
			for ($i = 0; $i < $count; $i++)
			{
				$key = trim($matches[0][$i], '[]');

				if ($key === '') // Empty notation will return the value as array
				{
					break;
				}

				if (isset($value[$key]))
				{
					$value = $value[$key];
				}
				else
				{
					return null;
				}
			}
		}

		if (! isset($value))
		{
			$value = $this->globals[$method][$index] ?? null;
		}

		// @phpstan-ignore-next-line
		if (is_array($value) && ($filter !== null || $flags !== null))
		{
			// Iterate over array and append filter and flags
			array_walk_recursive($value, function (&$val) use ($filter, $flags) {
				$val = filter_var($val, $filter, $flags);
			});

			return $value;
		}

		// Cannot filter these types of data automatically...
		if (is_array($value) || is_object($value) || is_null($value))
		{
			return $value;
		}

		return filter_var($value, $filter, $flags);
	}

	//--------------------------------------------------------------------

	/**
	 * Saves a copy of the current state of one of several PHP globals
	 * so we can retrieve them later.
	 *
	 * @param string $method
	 */
	protected function populateGlobals(string $method)
	{
		if (! isset($this->globals[$method]))
		{
			$this->globals[$method] = [];
		}

		// Don't populate ENV as it might contain
		// sensitive data that we don't want to get logged.
		switch($method)
		{
			case 'get':
				$this->globals['get'] = $_GET;
				break;
			case 'post':
				$this->globals['post'] = $_POST;
				break;
			case 'request':
				$this->globals['request'] = $_REQUEST;
				break;
			case 'cookie':
				$this->globals['cookie'] = $_COOKIE;
				break;
			case 'server':
				$this->globals['server'] = $_SERVER;
				break;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Magic getter to provide access to $this->uri since it
	 * used to be public.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get(string $name)
	{
		if ($name === 'uri')
		{
			return $this->getUri();
		}
	}
}
