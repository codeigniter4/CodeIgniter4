<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use CodeIgniter\HTTP\URI;
use CodeIgniter\Router\Exceptions\RouterException;
use Config\App;
use Config\Services;

/**
 * CodeIgniter URL Helpers
 */

if (! function_exists('site_url'))
{
	/**
	 * Return a site URL to use in views
	 *
	 * @param mixed       $uri       URI string or array of URI segments
	 * @param string|null $protocol
	 * @param App|null    $altConfig Alternate configuration to use
	 *
	 * @return string
	 */
	function site_url($uri = '', string $protocol = null, App $altConfig = null): string
	{
		// convert segment array to string
		if (is_array($uri))
		{
			$uri = implode('/', $uri);
		}

		// use alternate config if provided, else default one
		$config = $altConfig ?? config(App::class);

		$fullPath = rtrim(base_url(), '/') . '/';

		// Add index page, if so configured
		if (! empty($config->indexPage))
		{
			$fullPath .= rtrim($config->indexPage, '/');
		}
		if (! empty($uri))
		{
			$fullPath .= '/' . $uri;
		}

		$url = new URI($fullPath);

		// allow the scheme to be over-ridden; else, use default
		if (! empty($protocol))
		{
			$url->setScheme($protocol);
		}

		return (string) $url;
	}
}

//--------------------------------------------------------------------

if (! function_exists('base_url'))
{
	/**
	 * Return the base URL to use in views
	 *
	 * @param  mixed  $uri      URI string or array of URI segments
	 * @param  string $protocol
	 * @return string
	 */
	function base_url($uri = '', string $protocol = null): string
	{
		// convert segment array to string
		if (is_array($uri))
		{
			$uri = implode('/', $uri);
		}
		$uri = trim($uri, '/');

		// We should be using the configured baseURL that the user set;
		// otherwise get rid of the path, because we have
		// no way of knowing the intent...
		$config = Services::request()->config;

		// If baseUrl does not have a trailing slash it won't resolve
		// correctly for users hosting in a subfolder.
		$baseUrl = ! empty($config->baseURL) && $config->baseURL !== '/'
			? rtrim($config->baseURL, '/ ') . '/'
			: $config->baseURL;

		$url = new URI($baseUrl);
		unset($config);

		// Merge in the path set by the user, if any
		if (! empty($uri))
		{
			$url = $url->resolveRelativeURI($uri);
		}

		// If the scheme wasn't provided, check to
		// see if it was a secure request
		if (empty($protocol) && Services::request()->isSecure())
		{
			$protocol = 'https';
		}

		if (! empty($protocol))
		{
			$url->setScheme($protocol);
		}

		return rtrim((string) $url, '/ ');
	}
}

//--------------------------------------------------------------------

if (! function_exists('current_url'))
{
	/**
	 * Current URL
	 *
	 * Returns the full URL (including segments) of the page where this
	 * function is placed
	 *
	 * @param boolean $returnObject True to return an object instead of a strong
	 *
	 * @return string|\CodeIgniter\HTTP\URI
	 */
	function current_url(bool $returnObject = false)
	{
		$uri = clone Services::request()->uri;

		// Since we're basing off of the IncomingRequest URI,
		// we are guaranteed to have a host based on our own configs.
		return $returnObject ? $uri : (string) $uri->setQuery('');
	}
}

//--------------------------------------------------------------------

if (! function_exists('previous_url'))
{
	/**
	 * Returns the previous URL the current visitor was on. For security reasons
	 * we first check in a saved session variable, if it exists, and use that.
	 * If that's not available, however, we'll use a sanitized url from $_SERVER['HTTP_REFERER']
	 * which can be set by the user so is untrusted and not set by certain browsers/servers.
	 *
	 * @param boolean $returnObject
	 *
	 * @return URI|mixed|string
	 */
	function previous_url(bool $returnObject = false)
	{
		// Grab from the session first, if we have it,
		// since it's more reliable and safer.
		// Otherwise, grab a sanitized version from $_SERVER.
		$referer = $_SESSION['_ci_previous_url'] ?? Services::request()->getServer('HTTP_REFERER', FILTER_SANITIZE_URL);

		$referer = $referer ?? site_url('/');

		return $returnObject ? new URI($referer) : $referer;
	}
}

//--------------------------------------------------------------------

if (! function_exists('uri_string'))
{
	/**
	 * URL String
	 *
	 * Returns the path part of the current URL
	 *
	 * @param boolean $relative Whether the resulting path should be relative to baseURL
	 *
	 * @return string
	 */
	function uri_string(bool $relative = false): string
	{
		$request = Services::request();
		$uri     = $request->uri;

		// An absolute path is equivalent to getPath()
		if (! $relative)
		{
			return $uri->getPath();
		}

		// Remove the baseURL from the entire URL
		$url     = (string) $uri->__toString();
		$baseURL = rtrim($request->config->baseURL, '/ ') . '/';

		return substr($url, strlen($baseURL));
	}
}

//--------------------------------------------------------------------

if (! function_exists('index_page'))
{
	/**
	 * Index page
	 *
	 * Returns the "index_page" from your config file
	 *
	 * @param  \Config\App|null $altConfig Alternate configuration to use
	 * @return string
	 */
	function index_page(App $altConfig = null): string
	{
		// use alternate config if provided, else default one
		$config = $altConfig ?? config(App::class);

		return $config->indexPage;
	}
}

// ------------------------------------------------------------------------

if (! function_exists('anchor'))
{
	/**
	 * Anchor Link
	 *
	 * Creates an anchor based on the local URL.
	 *
	 * @param mixed    $uri        URI string or array of URI segments
	 * @param string   $title      The link title
	 * @param mixed    $attributes Any attributes
	 * @param App|null $altConfig  Alternate configuration to use
	 *
	 * @return string
	 */
	function anchor($uri = '', string $title = '', $attributes = '', App $altConfig = null): string
	{
		// use alternate config if provided, else default one
		$config = $altConfig ?? config(App::class);

		$siteUrl = is_array($uri) ? site_url($uri, null, $config) : (preg_match('#^(\w+:)?//#i', $uri) ? $uri : site_url($uri, null, $config));
		// eliminate trailing slash
		$siteUrl = rtrim($siteUrl, '/');

		if ($title === '')
		{
			$title = $siteUrl;
		}

		if ($attributes !== '')
		{
			$attributes = stringify_attributes($attributes);
		}

		return '<a href="' . $siteUrl . '"' . $attributes . '>' . $title . '</a>';
	}
}

// ------------------------------------------------------------------------

if (! function_exists('anchor_popup'))
{
	/**
	 * Anchor Link - Pop-up version
	 *
	 * Creates an anchor based on the local URL. The link
	 * opens a new window based on the attributes specified.
	 *
	 * @param string   $uri        the URL
	 * @param string   $title      the link title
	 * @param mixed    $attributes any attributes
	 * @param App|null $altConfig  Alternate configuration to use
	 *
	 * @return string
	 */
	function anchor_popup($uri = '', string $title = '', $attributes = false, App $altConfig = null): string
	{
		// use alternate config if provided, else default one
		$config = $altConfig ?? config(App::class);

		$siteUrl = preg_match('#^(\w+:)?//#i', $uri) ? $uri : site_url($uri, '', $config);
		$siteUrl = rtrim($siteUrl, '/');

		if ($title === '')
		{
			$title = $siteUrl;
		}

		if ($attributes === false)
		{
			return '<a href="' . $siteUrl . '" onclick="window.open(\'' . $siteUrl . "', '_blank'); return false;\">" . $title . '</a>';
		}

		if (! is_array($attributes))
		{
			$attributes = [$attributes];

			// Ref: http://www.w3schools.com/jsref/met_win_open.asp
			$windowName = '_blank';
		}
		elseif (! empty($attributes['window_name']))
		{
			$windowName = $attributes['window_name'];
			unset($attributes['window_name']);
		}
		else
		{
			$windowName = '_blank';
		}

		foreach (['width' => '800', 'height' => '600', 'scrollbars' => 'yes', 'menubar' => 'no', 'status' => 'yes', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0'] as $key => $val)
		{
			$atts[$key] = $attributes[$key] ?? $val;
			unset($attributes[$key]);
		}

		$attributes = stringify_attributes($attributes);

		return '<a href="' . $siteUrl
				. '" onclick="window.open(\'' . $siteUrl . "', '" . $windowName . "', '" . stringify_attributes($atts, true) . "'); return false;\""
				. $attributes . '>' . $title . '</a>';
	}
}

// ------------------------------------------------------------------------

if (! function_exists('mailto'))
{
	/**
	 * Mailto Link
	 *
	 * @param string $email      the email address
	 * @param string $title      the link title
	 * @param mixed  $attributes any attributes
	 *
	 * @return string
	 */
	function mailto(string $email, string $title = '', $attributes = ''): string
	{
		if (trim($title) === '')
		{
			$title = $email;
		}

		return '<a href="mailto:' . $email . '"' . stringify_attributes($attributes) . '>' . $title . '</a>';
	}
}

// ------------------------------------------------------------------------

if (! function_exists('safe_mailto'))
{
	/**
	 * Encoded Mailto Link
	 *
	 * Create a spam-protected mailto link written in Javascript
	 *
	 * @param string $email      the email address
	 * @param string $title      the link title
	 * @param mixed  $attributes any attributes
	 *
	 * @return string
	 */
	function safe_mailto(string $email, string $title = '', $attributes = ''): string
	{
		if (trim($title) === '')
		{
			$title = $email;
		}

		$x = str_split('<a href="mailto:', 1);

		for ($i = 0, $l = strlen($email); $i < $l; $i ++)
		{
			$x[] = '|' . ord($email[$i]);
		}

		$x[] = '"';

		if ($attributes !== '')
		{
			if (is_array($attributes))
			{
				foreach ($attributes as $key => $val)
				{
					$x[] = ' ' . $key . '="';
					for ($i = 0, $l = strlen($val); $i < $l; $i ++)
					{
						$x[] = '|' . ord($val[$i]);
					}
					$x[] = '"';
				}
			}
			else
			{
				for ($i = 0, $l = mb_strlen($attributes); $i < $l; $i ++)
				{
					$x[] = mb_substr($attributes, $i, 1);
				}
			}
		}

		$x[] = '>';

		$temp = [];
		for ($i = 0, $l = strlen($title); $i < $l; $i ++)
		{
			$ordinal = ord($title[$i]);

			if ($ordinal < 128)
			{
				$x[] = '|' . $ordinal;
			}
			else
			{
				if (empty($temp))
				{
					$count = ($ordinal < 224) ? 2 : 3;
				}

				$temp[] = $ordinal;
				if (count($temp) === $count) // @phpstan-ignore-line
				{
					$number = ($count === 3) ? (($temp[0] % 16) * 4096) + (($temp[1] % 64) * 64) + ($temp[2] % 64) : (($temp[0] % 32) * 64) + ($temp[1] % 64);
					$x[]    = '|' . $number;
					$count  = 1;
					$temp   = [];
				}
			}
		}

		$x[] = '<';
		$x[] = '/';
		$x[] = 'a';
		$x[] = '>';

		$x = array_reverse($x);

		// improve obfuscation by eliminating newlines & whitespace
		$output = '<script type="text/javascript">'
				. 'var l=new Array();';

		for ($i = 0, $c = count($x); $i < $c; $i ++)
		{
			$output .= 'l[' . $i . "] = '" . $x[$i] . "';";
		}

		return $output . ('for (var i = l.length-1; i >= 0; i=i-1) {'
				. "if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");"
				. 'else document.write(unescape(l[i]));'
				. '}'
				. '</script>');
	}
}

// ------------------------------------------------------------------------

if (! function_exists('auto_link'))
{
	/**
	 * Auto-linker
	 *
	 * Automatically links URL and Email addresses.
	 * Note: There's a bit of extra code here to deal with
	 * URLs or emails that end in a period. We'll strip these
	 * off and add them after the link.
	 *
	 * @param string  $str   the string
	 * @param string  $type  the type: email, url, or both
	 * @param boolean $popup whether to create pop-up links
	 *
	 * @return string
	 */
	function auto_link(string $str, string $type = 'both', bool $popup = false): string
	{
		// Find and replace any URLs.
		if ($type !== 'email' && preg_match_all('#(\w*://|www\.)[^\s()<>;]+\w#i', $str, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER))
		{
			// Set our target HTML if using popup links.
			$target = ($popup) ? ' target="_blank"' : '';

			// We process the links in reverse order (last -> first) so that
			// the returned string offsets from preg_match_all() are not
			// moved as we add more HTML.
			foreach (array_reverse($matches) as $match)
			{
				// $match[0] is the matched string/link
				// $match[1] is either a protocol prefix or 'www.'
				//
				// With PREG_OFFSET_CAPTURE, both of the above is an array,
				// where the actual value is held in [0] and its offset at the [1] index.
				$a   = '<a href="' . (strpos($match[1][0], '/') ? '' : 'http://') . $match[0][0] . '"' . $target . '>' . $match[0][0] . '</a>';
				$str = substr_replace($str, $a, $match[0][1], strlen($match[0][0]));
			}
		}

		// Find and replace any emails.
		if ($type !== 'url' && preg_match_all('#([\w\.\-\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+[^[:punct:]\s])#i', $str, $matches, PREG_OFFSET_CAPTURE))
		{
			foreach (array_reverse($matches[0]) as $match)
			{
				if (filter_var($match[0], FILTER_VALIDATE_EMAIL) !== false)
				{
					$str = substr_replace($str, safe_mailto($match[0]), $match[1], strlen($match[0]));
				}
			}
		}

		return $str;
	}
}

// ------------------------------------------------------------------------

if (! function_exists('prep_url'))
{
	/**
	 * Prep URL - Simply adds the http:// or https:// part if no scheme is included.
	 *
	 * Formerly used URI, but that does not play nicely with URIs missing
	 * the scheme.
	 *
	 * @param  string  $str    the URL
	 * @param  boolean $secure set true if you want to force https://
	 * @return string
	 */
	function prep_url(string $str = '', bool $secure = false): string
	{
		if (in_array($str, ['http://', 'https://', '//', ''], true))
		{
			return '';
		}

		if (parse_url($str, PHP_URL_SCHEME) === null)
		{
			$str = 'http://' . ltrim($str, '/');
		}

		// force replace http:// with https://
		if ($secure)
		{
			$str = preg_replace('/^(?:http):/i', 'https:', $str);
		}

		return $str;
	}
}

// ------------------------------------------------------------------------

if (! function_exists('url_title'))
{
	/**
	 * Create URL Title
	 *
	 * Takes a "title" string as input and creates a
	 * human-friendly URL string with a "separator" string
	 * as the word separator.
	 *
	 * @param  string  $str       Input string
	 * @param  string  $separator Word separator (usually '-' or '_')
	 * @param  boolean $lowercase Whether to transform the output string to lowercase
	 * @return string
	 */
	function url_title(string $str, string $separator = '-', bool $lowercase = false): string
	{
		$qSeparator = preg_quote($separator, '#');

		$trans = [
			'&.+?;'                  => '',
			'[^\w\d\pL\pM _-]'       => '',
			'\s+'                    => $separator,
			'(' . $qSeparator . ')+' => $separator,
		];

		$str = strip_tags($str);
		foreach ($trans as $key => $val)
		{
			$str = preg_replace('#' . $key . '#iu', $val, $str);
		}

		if ($lowercase === true)
		{
			$str = mb_strtolower($str);
		}

		return trim(trim($str, $separator));
	}
}

// ------------------------------------------------------------------------

if (! function_exists('mb_url_title'))
{
	/**
	 * Create URL Title that takes into account accented characters
	 *
	 * Takes a "title" string as input and creates a
	 * human-friendly URL string with a "separator" string
	 * as the word separator.
	 *
	 * @param  string  $str       Input string
	 * @param  string  $separator Word separator (usually '-' or '_')
	 * @param  boolean $lowercase Whether to transform the output string to lowercase
	 * @return string
	 */
	function mb_url_title(string $str, string $separator = '-', bool $lowercase = false): string
	{
		helper('text');

		return url_title(convert_accented_characters($str), $separator, $lowercase);
	}
}

//--------------------------------------------------------------------

if (! function_exists('url_to'))
{
	/**
	 * Get the full, absolute URL to a controller method
	 * (with additional arguments)
	 *
	 * @param string $controller
	 * @param mixed  ...$args
	 *
	 * @throws RouterException
	 *
	 * @return string
	 */
	function url_to(string $controller, ...$args): string
	{
		if (! $route = route_to($controller, ...$args))
		{
			$explode = explode('::', $controller);

			if (isset($explode[1]))
			{
				throw RouterException::forControllerNotFound($explode[0], $explode[1]);
			}

			throw RouterException::forInvalidRoute($controller);
		}

		return site_url($route);
	}
}

if (! function_exists('url_is'))
{
	/**
	 * Determines if current url path contains
	 * the given path. It may contain a wildcard (*)
	 * which will allow any valid character.
	 *
	 * Example:
	 *   if (url_is('admin*)) ...
	 *
	 * @param string $path
	 *
	 * @return boolean
	 */
	function url_is(string $path): bool
	{
		// Setup our regex to allow wildcards
		$path        = '/' . trim(str_replace('*', '(\S)*', $path), '/ ');
		$currentPath = '/' . trim(uri_string(true), '/ ');

		return (bool) preg_match("|^{$path}$|", $currentPath, $matches);
	}
}
