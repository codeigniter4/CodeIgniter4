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
	protected $timeout = 2.0;

	protected $response;

	/**
	 * The first poriton of URI that is prepended
	 * to all relative requests.
	 * @var string
	 */
	protected $base_uri;

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
	public function __construct(AppConfig $config, URI $uri, ResponseInterface $response=null, array $options=[])
	{
		if (! function_exists('curl_version'))
		{
			throw new \RuntimeException('CURL must be enabled to use the CURLRequest class.');
		}

		parent::__construct($config);

		$this->response = $response;

		if (array_key_exists('base_uri', $options))
		{
			$this->base_uri = $uri->setURI($options['base_uri']);
		}

		$this->parseOptions($options);
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
		$this->parseOptions($options);

		$url = $this->prepareURL($url);

		$this->send($url);

		return $this->response;
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

	/**
	 * Sets the correct settings based on the options array
	 * passed in.
	 *
	 * @param array $options
	 */
	protected function parseOptions(array $options)
	{
		foreach ($options as $key => $value)
		{
			if (isset($this->$key))
			{
				$this->$key = $value;
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * If the $url is a relative URL, will attempt to create
	 * a full URL by prepending $this->base_uri to it.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	protected function prepareURL(string $url): string
	{
		// If it's a full URI, then we have nothing to do here...
		if (strpos($url, '://') !== false)
		{
			return $url;
		}

		return (string)$this->base_uri->resolveRelativeURI($url);
	}

	//--------------------------------------------------------------------

	/**
	 * Fires the actual cURL request.
	 *
	 * @param string $url
	 */
	public function send(string $url)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HEADER, true);

		// Send the request and wait for a response.
		$output = curl_exec($ch);

		if($output === false)
		{
			echo "Error Number:".curl_errno($ch)."<br>";
			echo "Error String:".curl_error($ch);
		}

		curl_close($ch);

		// Split out our headers and body
		$break = strpos($output, "\r\n\r\n");

		if ($break !== false)
		{
			// Our headers
			$headers = explode("\n", substr($output, 0, $break));

			$this->setResponseHeaders($headers);

			// Our body
			$body = substr($output, $break+4);
			$this->response->setBody($body);
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Parses the header retrieved from the cURL response into
	 * our Response object.
	 *
	 * @param array $headers
	 */
	protected function setResponseHeaders(array $headers = [])
	{
		foreach ($headers as $header)
		{
			if (($pos = strpos($header, ':')) !== false)
			{
				$title = substr($header, 0, $pos);
				$value = substr($header, $pos+1);

				$this->response->setHeader($title, $value);
			}
			else if (substr($header, 0, 4) == 'HTTP')
			{
				preg_match('#^HTTP\/(1\.[01]) ([0-9]+) (.+)#', $header, $matches);

				if (isset($matches[1]))
				{
					$this->response->setProtocolVersion($matches[1]);
				}

				if (isset($matches[2]))
				{
					$this->response->setStatusCode($matches[2], isset($matches[3]) ? $matches[3] : null);
				}
			}
		}
	}

	//--------------------------------------------------------------------

}
