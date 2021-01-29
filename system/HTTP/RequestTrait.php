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

use CodeIgniter\Validation\FormatRules;

/**
 * Request Trait
 *
 * Additional methods to make a PSR-7 Request class
 * compliant with the framework's own RequestInterface.
 *
 * @see https://github.com/php-fig/http-message/blob/master/src/RequestInterface.php
 */
trait RequestTrait
{
	/**
	 * IP address of the current user.
	 *
	 * @var string
	 *
	 * @deprecated Will become private in a future release
	 */
	protected $ipAddress = '';

	/**
	 * Stores values we've retrieved from
	 * PHP globals.
	 *
	 * @var array
	 */
	protected $globals = [];

	/**
	 * Gets the user's IP address.
	 *
	 * @return string IP address
	 */
	public function getIPAddress(): string
	{
		if ($this->ipAddress)
		{
			return $this->ipAddress;
		}

		$ipValidator = [
			new FormatRules(),
			'valid_ip',
		];

		/**
		 * @deprecated $this->proxyIPs property will be removed in the future
		 */
		$proxyIPs = isset($this->proxyIPs) ? $this->proxyIPs : config('App')->proxyIPs;
		if (! empty($proxyIPs) && ! is_array($proxyIPs))
		{
			$proxyIPs = explode(',', str_replace(' ', '', $proxyIPs));
		}

		$this->ipAddress = $this->getServer('REMOTE_ADDR');

		if ($proxyIPs)
		{
			foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP'] as $header)
			{
				if (($spoof = $this->getServer($header)) !== null)
				{
					// Some proxies typically list the whole chain of IP
					// addresses through which the client has reached us.
					// e.g. client_ip, proxy_ip1, proxy_ip2, etc.
					sscanf($spoof, '%[^,]', $spoof);

					if (! $ipValidator($spoof))
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
				foreach ($proxyIPs as $proxyIP)
				{
					// Check if we have an IP address or a subnet
					if (strpos($proxyIP, '/') === false)
					{
						// An IP address (and not a subnet) is specified.
						// We can compare right away.
						if ($proxyIP === $this->ipAddress)
						{
							$this->ipAddress = $spoof;
							break;
						}

						continue;
					}

					// We have a subnet ... now the heavy lifting begins
					if (! isset($separator))
					{
						$separator = $ipValidator($this->ipAddress, 'ipv6') ? ':' : '.';
					}

					// If the proxy entry doesn't match the IP protocol - skip it
					if (strpos($proxyIP, $separator) === false)
					{
						continue;
					}

					// Convert the REMOTE_ADDR IP address to binary, if needed
					if (! isset($ip, $sprintf))
					{
						if ($separator === ':')
						{
							// Make sure we're have the "full" IPv6 format
							$ip = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($this->ipAddress, ':')), $this->ipAddress));

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
					sscanf($proxyIP, '%[^/]/%d', $netaddr, $masklen);

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
						$this->ipAddress = $spoof;
						break;
					}
				}
			}
		}

		if (! $ipValidator($this->ipAddress))
		{
			return $this->ipAddress = '0.0.0.0';
		}

		return empty($this->ipAddress) ? '' : $this->ipAddress;
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
}
