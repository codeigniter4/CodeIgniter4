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
	/**
	 * @var ResponseInterface
	 */
	protected $response;

	/**
	 * @var URI
	 */
	protected $base_uri;

	/**
	 * The setting values
	 * @var array
	 */
	protected $config = [
	    'timeout' => 0.0,
	    'connect_timeout' => 150,
	    'debug' => false
	];

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
		$this->base_uri = $uri;

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

		$method = filter_var($method, FILTER_SANITIZE_STRING);

		$this->send($method, $url);

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
		if (array_key_exists('base_uri', $options))
		{
			$this->base_uri = $this->base_uri->setURI($options['base_uri']);
			unset($options['base_uri']);
		}

		if (array_key_exists('headers', $options) && is_array($options['headers']))
		{
			foreach ($options['headers'] as $name => $value)
			{
				$this->setHeader($name, $value);
			}

			unset($options['headers']);
		}

		foreach ($options as $key => $value)
		{
			$this->config[$key] = $value;
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

		$uri = $this->base_uri->resolveRelativeURI($url);

		return (string)$uri;
	}

	//--------------------------------------------------------------------

	/**
	 * Get the request method. Overrides the Request class' method
	 * since users expect a different answer here.
	 *
	 * @param bool|false $upper Whether to return in upper or lower case.
	 *
	 * @return string
	 */
	public function method($upper = false): string
	{
		return ($upper)
				? strtoupper($this->method)
				: strtolower($this->method);
	}

	//--------------------------------------------------------------------


	/**
	 * Fires the actual cURL request.
	 *
	 * @param string $url
	 */
	public function send(string $method, string $url)
	{
		// Reset our curl options so we're on a fresh slate.
		$curl_options = [];

		$curl_options[CURLOPT_URL] = $url;
		$curl_options[CURLOPT_RETURNTRANSFER] = true;
		$curl_options[CURLOPT_HEADER] = true;
		$curl_options[CURLOPT_FRESH_CONNECT] = true;

		$curl_options = $this->setCURLOptions($curl_options, $this->config);
		$curl_options = $this->applyMethod($method, $curl_options);
		$curl_options = $this->applyRequestHeaders($curl_options);

		$output = $this->sendRequest($curl_options);

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
		else
		{
			$this->response->setBody($output);
		}

		return $this->response;
	}

	//--------------------------------------------------------------------

	/**
	 * Takes all headers current part of this request and adds them
	 * to the cURL request.
	 *
	 * @param array $curl_options
	 */
	protected function applyRequestHeaders(array $curl_options=[]): array
	{
	    $headers = $this->headers();

		if (empty($head)) return $curl_options;

		$set = [];

		foreach ($headers as $name => $value)
		{
			$set[] = $name.': '. $this->headerLine($name);
		}

		$curl_options[CURLOPT_HTTPHEADER] = $set;

		return $curl_options;
	}

	//--------------------------------------------------------------------

	protected function applyMethod($method, array $curl_options): array
	{
		$method = strtoupper($method);

		$this->method = $method;
		$curl_options[CURLOPT_CUSTOMREQUEST] = $method;

		$size = strlen($this->body);

		// Have content?
		if ($size === null || $size > 0)
		{
			$curl_options = $this->applyBody($curl_options);
			return $curl_options;
		}

		if ($method == 'PUT' || $method == 'POST')
		{
			// See http://tools.ietf.org/html/rfc7230#section-3.3.2
			if (is_null($this->header('content-length')))
			{
				$this->setHeader('Content-Length', 0);
			}
		}
		else if ($method == 'HEAD')
		{
			$curl_options[CURLOPT_NOBODY] = 1;
		}

		return $curl_options;
	}

	//--------------------------------------------------------------------

	protected function applyBody(array $curl_options=[]): array
	{
		if (! empty($this->body))
		{
			$curl_options[CURLOPT_POSTFIELDS] = (string)$this->body();
		}

		// curl sometimes adds a content type by default, prevent this
		$this->setHeader('Content-Type', '');

		return $curl_options;
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

	protected function setCURLOptions(array $curl_options=[], array $config=[])
	{
		// Auth Headers
		if (! empty($config['auth']))
		{
			$curl_options[CURLOPT_USERPWD] = $config['auth'][0].':'.$config['auth'][1];

			if (! empty($config['auth'][2]) && strtolower($config['auth'][2]) == 'digest')
			{
				$curl_options[CURLOPT_HTTPAUTH] = CURLAUTH_DIGEST;
			}
			else
			{
				$curl_options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
			}
		}

		// Certificate
		if (! empty($config['cert']))
		{
			$cert = $config['cert'];

			if (is_array($cert))
			{
				$curl_options[CURLOPT_SSLCERTPASSWD] = $cert[1];
				$cert = $cert[0];
			}

			if (! file_exists($cert))
			{
				throw new \InvalidArgumentException('SSL certificate not found at: '. $cert);
			}

			$curl_options[CURLOPT_SSLCERT] = $cert;
		}

		// Debug
		if (isset($config['debug']))
		{
			$curl_options[CURLOPT_VERBOSE] = 1;
			$curl_options[CURLOPT_STDERR] = is_bool($config['debug']) ? fopen('php://output', 'w+') : $config['debug'];
		}

		// Decode Content
		if (! empty($config['decode_content']))
		{
			$accept = $this->headerLine('Accept-Encoding');

			if ($accept)
			{
				$curl_options[CURLOPT_ENCODING] = $accept;
			}
			else
			{
				$curl_options[CURLOPT_ENCODING] = '';
				$curl_options[CURLOPT_HTTPHEADER] = 'Accept-Encoding';
			}
		}

		// Timeout
		$curl_options[CURLOPT_TIMEOUT_MS] = (float)$config['timeout'] * 1000;

		// Connection Timeout
		$curl_options[CURLOPT_CONNECTTIMEOUT_MS] = (float)$config['connect_timeout'] * 1000;

		return $curl_options;
	}

	//--------------------------------------------------------------------

	/**
	 * Does the actual work of initializing cURL, setting the options,
	 * and grabbing the output.
	 *
	 * @param array $curl_options
	 *
	 * @return string
	 */
	protected function sendRequest(array $curl_options = []): string
	{
		$ch = curl_init();

		curl_setopt_array($ch, $curl_options);

		// Send the request and wait for a response.
		$output = curl_exec($ch);

		if($output === false)
		{
			throw new \RuntimeException(curl_errno($ch) .': '. curl_error($ch));
		}

		curl_close($ch);

		return $output;
	}

	//--------------------------------------------------------------------

}
