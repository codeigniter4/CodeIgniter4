<?php namespace CodeIgniter\HTTP;

use App\Config\AppConfig;

/**
 * Class OutgoingRequest
 *
 * A lightweight HTTP client for sending synchronous HTTP requests
 * via cURL.
 *
 * @todo    Add a few helpers for dealing with JSON, forms, files, etc.
 *
 * @package CodeIgniter\HTTPLite
 */
class CURLRequest extends Request
{
	protected $baseURI;

	protected $timeout = 2.0;

	//--------------------------------------------------------------------

	/**
	 * Takes an array of options to set the following possible class properties:
	 *
	 *  - baseURI
	 *  - timeout
	 *  - any other request options to use as defaults.
	 *
	 * @param array $options
	 */
	public function __construct(AppConfig $config, $uri = null, $body = null)
	{
		if (! function_exists('curl_version'))
		{
			throw new \RuntimeException('CURL must be enabled to use the CURLRequest class.');
		}

		parent::__construct($config, $uri, $body);
	}

	//--------------------------------------------------------------------

	/**
	 * Sends an HTTP request to the specified $url. If this is a relative
	 * URL, it will be merged with $this->baseURI to form a complete URL.
	 *
	 * @param            $method
	 * @param string     $url
	 * @param array      $options
	 *
	 * @return Response
	 */
	public function request($method, string $url, array $options = []): Response
	{
	}

	//--------------------------------------------------------------------

	/**
	 * Convenience method for sending a GET request.
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public function get(string $url, array $options = []): Response
	{
		return $this->request('get', $url, $options);
	}

	//--------------------------------------------------------------------

	/**
	 * Convenience method for sending a DELETE request.
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public function delete(string $url, array $options = []): Response
	{
		return $this->request('delete', $url, $options);
	}

	//--------------------------------------------------------------------

	/**
	 * Convenience method for sending a HEAD request.
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public function head(string $url, array $options = []): Response
	{
		return $this->request('head', $url, $options);
	}

	//--------------------------------------------------------------------

	/**
	 * Convenience method for sending an OPTIONS request.
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public function options(string $url, array $options = []): Response
	{
		return $this->request('options', $url, $options);
	}

	//--------------------------------------------------------------------

	/**
	 * Convenience method for sending a PATCH request.
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public function patch(string $url, array $options = []): Response
	{
		return $this->request('patch', $url, $options);
	}

	//--------------------------------------------------------------------

	/**
	 * Convenience method for sending a POST request.
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public function post(string $url, array $options = []): Response
	{
		return $this->request('post', $url, $options);
	}

	//--------------------------------------------------------------------

	/**
	 * Convenience method for sending a PUT request.
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public function put(string $url, array $options = []): Response
	{
		return $this->request('put', $url, $options);
	}

	//--------------------------------------------------------------------

}
