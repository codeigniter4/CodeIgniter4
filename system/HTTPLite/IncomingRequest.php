<?php namespace CodeIgniter\HTTPLite;

/**
 * Class IncomingRequest
 *
 * Represents an incoming, server-side HTTP request.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 *
 * Additionally, it encapsulates all data as it has arrived to the
 * application from the CGI and/or PHP environment, including:
 *
 * - The values represented in $_SERVER.
 * - Any cookies provided (generally via $_COOKIE)
 * - Query string arguments (generally via $_GET, or as parsed via parse_str())
 * - Upload files, if any (as represented by $_FILES)
 * - Deserialized body parameters (generally from $_POST)
 *
 * @package CodeIgniter\HTTPLite
 */
class IncomingRequest extends Request {

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