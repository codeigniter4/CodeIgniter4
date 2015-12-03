<?php namespace CodeIgniter\HTTP;

require_once BASEPATH.'Config/BaseConfig.php';
require_once APPPATH.'/config/AppConfig.php';

use App\Config\AppConfig;

class Request extends Message implements RequestInterface
{
	/**
	 * IP address of the current user.
	 *
	 * @var string
	 */
	protected $ipAddress = '';

	protected $proxyIPs;

	//--------------------------------------------------------------------

	public function __construct(AppConfig $config, $uri=null)
	{
	    $this->proxyIPs = $config->proxyIPs;
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

		$proxy_ips = $this->proxyIPs;
		if ( ! empty($this->proxyIPs) && ! is_array($this->proxyIPs))
		{
			$proxy_ips = explode(',', str_replace(' ', '', $this->proxyIPs));
		}

		$this->ipAddress = $this->server('REMOTE_ADDR');

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

					if ( ! $this->isValidIP($spoof))
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
				for ($i = 0, $c = count($this->proxyIPs); $i < $c; $i++)
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
					isset($separator) OR $separator = $this->isValidIP($this->ipAddress, 'ipv6') ? ':' : '.';

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
						$this->ipAddress = $spoof;
						break;
					}
				}
			}
		}

		if ( ! $this->isValidIP($this->ipAddress))
		{
			return $this->ipAddress = '0.0.0.0';
		}

		return empty($this->ipAddress) ? '' : $this->ipAddress;
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
	public function isValidIP(string $ip, string $which = null): bool
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
	public function getMethod($upper = false): string
	{
		return ($upper)
			? strtoupper($this->server('REQUEST_METHOD'))
			: strtolower($this->server('REQUEST_METHOD'));
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
		// Null filters cause null values to return.
		if (is_null($filter))
		{
			$filter = FILTER_DEFAULT;
		}

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
				$output[$key] = $this->fetchGlobal($type, $key, $filter);
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

		// Due to issues with FastCGI and testing,
		// we need to do these all manually instead
		// of the simpler filter_input();
		switch ($type)
		{
			case INPUT_GET:
				$value = isset($_GET[$index]) ? $_GET[$index] : null;
				break;
			case INPUT_POST:
				$value = isset($_POST[$index]) ? $_POST[$index] : null;
				break;
			case INPUT_SERVER:
				$value = isset($_SERVER[$index]) ? $_SERVER[$index] : null;
				break;
			case INPUT_ENV:
				$value = isset($_ENV[$index]) ? $_ENV[$index] : null;
				break;
			case INPUT_COOKIE:
				$value = isset($_COOKIE[$index]) ? $_COOKIE[$index] : null;
				break;
			case INPUT_REQUEST:
				$value = isset($_REQUEST[$index]) ? $_REQUEST[$index] : null;
				break;
			case INPUT_SESSION:
				$value = isset($_SESSION[$index]) ? $_SESSION[$index] : null;
				break;
			default:
				$value = '';
		}

		if (is_array($value) || is_object($value))
		{
			return $value;
		}

		return filter_var($value, $filter);
	}

	//--------------------------------------------------------------------
}