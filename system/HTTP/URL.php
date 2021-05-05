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

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Router\Exceptions\RouterException;
use Config\App;
use Config\Services;
use InvalidArgumentException;

/**
 * URI wrapper to work with complete project URLs.
 * This class creates immutable instances.
 */
final class URL
{
	/**
	 * Explicit URL to use for current.
	 *
	 * @var static|null
	 */
	private static $current;

	/**
	 * Underlying URI instance
	 *
	 * @var URI
	 */
	private $uri;

	/**
	 * Path relative to baseURL
	 *
	 * @var string
	 */
	private $relativePath;

	//--------------------------------------------------------------------

	/**
	 * Creates the base URL.
	 *
	 * @return static
	 */
	public static function base()
	{
		return static::public('');
	}

	/**
	 * Creates a URL to unrouted public files (typically assets).
	 * Similar to base_url('path/to/file')
	 *
	 * @param string $uri Additional URI string to include
	 *
	 * @return static
	 */
	public static function public(string $uri)
	{
		// Base URLs never include the index page
		$config            = clone config('App');
		$config->indexPage = '';

		return new static($uri, $config);
	}

	/**
	 * Returns an instance representing the current URL.
	 *
	 * @return static
	 */
	public static function current()
	{
		if (isset(self::$current))
		{
			return self::$current;
		}

		return static::fromRequest(Services::request());
	}

	/**
	 * Creates a framework URL.
	 *
	 * @param string $uri
	 *
	 * @return static
	 */
	public static function to(string $uri)
	{
		return new static(rtrim($uri, '/ '));
	}

	/**
	 * Creates a URL to a named or reverse route.
	 *
	 * @param string $uri Named or reverse route
	 *
	 * @return static
	 *
	 * @throws RouterException
	 */
	public static function route(string $uri)
	{
		// Check for a named or reverse-route
		if ($route = Services::routes()->reverseRoute($uri))
		{
			return new static($route);
		}

		throw RouterException::forInvalidRoute($uri);
	}

	/**
	 * Returns an instance representing the URL from
	 * an Incoming Request (as defined by Config\App).
	 *
	 * @param IncomingRequest $request
	 *
	 * @return static
	 */
	public static function fromRequest(IncomingRequest $request)
	{
		$path  = $request->detectPath($request->config->uriProtocol);
		$query = isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';

		return new static($path . $query, $request->config);
	}

	/**
	 * Injects a URL to use for the current.
	 * Useful for testing components whose behavior
	 * depends on the URL being visited.
	 *
	 * @param URL|null $url
	 */
	public static function setCurrent(?URL $url)
	{
		self::$current = $url;
	}

	//--------------------------------------------------------------------

	/**
	 * Store the App configuration and create the URI
	 * instance from the relative path.
	 *
	 * @param string   $relativePath
	 * @param App|null $config
	 */
	public function __construct(string $relativePath = '', App $config = null)
	{
		$config = $config ?? config('App');

		if ($config->baseURL === '')
		{
			throw new InvalidArgumentException('URL class requires a valid baseURL.');
		}
		if (is_int(strpos($relativePath, '://')))
		{
			throw new InvalidArgumentException('URL class only accepts relative paths.');
		}

		$this->relativePath = ltrim(URI::removeDotSegments($relativePath), '/');

		// Build the full URL based on $config and $relativePath
		$url = rtrim($config->baseURL, '/ ') . '/';

		// Check for an index page
		if ($config->indexPage !== '')
		{
			$url .= $config->indexPage;

			// If the relative path has anything other than ? (for query) we need a separator
			if ($this->relativePath !== '' && strncmp($this->relativePath, '?', 1) !== 0)
			{
				$url .= '/';
			}
		}

		$url .= $this->relativePath;

		$this->uri = new URI($url);

		// Check if the baseURL scheme needs to be coerced into its secure version
		if ($config->forceGlobalSecureRequests && $this->uri->getScheme() === 'http')
		{
			$this->uri->setScheme('https');
		}
	}

	/**
	 * Returns the path relative to baseUrl, loosely
	 * what was passed to the constructor.
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->relativePath;
	}

	/**
	 * Returns the underlying URI instance.
	 * In order for this instance to remain
	 * immutable a clone is returned.
	 *
	 * @return URI
	 */
	public function getUri(): URI
	{
		return clone $this->uri;
	}

	/**
	 * Returns this URL as a string.
	 * Since this is typically for routing and
	 * link purposes we strip any queries, but
	 * they can be accessed via getUri().
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) $this->getUri()->setQuery('');
	}
}
