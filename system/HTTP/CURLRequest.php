<?php


/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use Config\App;

/**
 * Class OutgoingRequest
 *
 * A lightweight HTTP client for sending synchronous HTTP requests
 * via cURL.
 *
 * @package CodeIgniter\HTTP
 */
class CURLRequest extends Request
{

	/**
	 * The response object associated with this request
	 *
	 * @var \CodeIgniter\HTTP\Response
	 */
	protected $response;

	/**
	 * The URI associated with this request
	 *
	 * @var URI
	 */
	protected $baseURI;

	/**
	 * The setting values
	 *
	 * @var array
	 */
	protected $config = [
		'timeout'         => 0.0,
		'connect_timeout' => 150,
		'debug'           => false,
		'verify'          => true,
	];

	/**
	 * Default values for when 'allow_redirects'
	 * option is true.
	 *
	 * @var array
	 */
	protected $redirectDefaults = [
		'max'       => 5,
		'strict'    => true,
		'protocols' => [
			'http',
			'https',
		],
	];

	/**
	 * The number of milliseconds to delay before
	 * sending the request.
	 *
	 * @var float
	 */
	protected $delay = 0.0;

	//--------------------------------------------------------------------

	/**
	 * Takes an array of options to set the following possible class properties:
	 *
	 *  - baseURI
	 *  - timeout
	 *  - any other request options to use as defaults.
	 *
	 * @param App               $config
	 * @param URI               $uri
	 * @param ResponseInterface $response
	 * @param array             $options
	 */
	public function __construct(App $config, URI $uri, ResponseInterface $response = null, array $options = [])
	{
		if (! function_exists('curl_version'))
		{
			// we won't see this during travis-CI
			// @codeCoverageIgnoreStart
			throw HTTPException::forMissingCurl();
			// @codeCoverageIgnoreEnd
		}

		parent::__construct($config);

		$this->response = $response;
		$this->baseURI  = $uri;

		$this->parseOptions($options);
	}

	//--------------------------------------------------------------------

	/**
	 * Sends an HTTP request to the specified $url. If this is a relative
	 * URL, it will be merged with $this->baseURI to form a complete URL.
	 *
	 * @param $method
	 * @param string $url
	 * @param array  $options
	 *
	 * @return \CodeIgniter\HTTP\ResponseInterface
	 */
	public function request($method, string $url, array $options = []): ResponseInterface
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
	 * @return \CodeIgniter\HTTP\ResponseInterface
	 */
	public function get(string $url, array $options = []): ResponseInterface
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
	 * @return \CodeIgniter\HTTP\ResponseInterface
	 */
	public function delete(string $url, array $options = []): ResponseInterface
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
	 * @return ResponseInterface
	 */
	public function head(string $url, array $options = []): ResponseInterface
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
	 * @return \CodeIgniter\HTTP\ResponseInterface
	 */
	public function options(string $url, array $options = []): ResponseInterface
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
	 * @return \CodeIgniter\HTTP\ResponseInterface
	 */
	public function patch(string $url, array $options = []): ResponseInterface
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
	 * @return \CodeIgniter\HTTP\ResponseInterface
	 */
	public function post(string $url, array $options = []): ResponseInterface
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
	 * @return \CodeIgniter\HTTP\ResponseInterface
	 */
	public function put(string $url, array $options = []): ResponseInterface
	{
		return $this->request('put', $url, $options);
	}

	//--------------------------------------------------------------------

	/**
	 * Set the HTTP Authentication.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $type     basic or digest
	 *
	 * @return $this
	 */
	public function setAuth(string $username, string $password, string $type = 'basic')
	{
		$this->config['auth'] = [
			$username,
			$password,
			$type,
		];

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set form data to be sent.
	 *
	 * @param array   $params
	 * @param boolean $multipart Set TRUE if you are sending CURLFiles
	 *
	 * @return $this
	 */
	public function setForm(array $params, bool $multipart = false)
	{
		if ($multipart)
		{
			$this->config['multipart'] = $params;
		}
		else
		{
			$this->config['form_params'] = $params;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set JSON data to be sent.
	 *
	 * @param mixed $data
	 *
	 * @return $this
	 */
	public function setJSON($data)
	{
		$this->config['json'] = $data;

		return $this;
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
		if (array_key_exists('baseURI', $options))
		{
			$this->baseURI = $this->baseURI->setURI($options['baseURI']);
			unset($options['baseURI']);
		}

		if (array_key_exists('headers', $options) && is_array($options['headers']))
		{
			foreach ($options['headers'] as $name => $value)
			{
				$this->setHeader($name, $value);
			}

			unset($options['headers']);
		}

		if (array_key_exists('delay', $options))
		{
			// Convert from the milliseconds passed in
			// to the seconds that sleep requires.
			$this->delay = (float) $options['delay'] / 1000;
			unset($options['delay']);
		}

		foreach ($options as $key => $value)
		{
			$this->config[$key] = $value;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * If the $url is a relative URL, will attempt to create
	 * a full URL by prepending $this->baseURI to it.
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

		$uri = $this->baseURI->resolveRelativeURI($url);

		return (string) $uri;
	}

	//--------------------------------------------------------------------

	/**
	 * Get the request method. Overrides the Request class' method
	 * since users expect a different answer here.
	 *
	 * @param boolean|false $upper Whether to return in upper or lower case.
	 *
	 * @return string
	 */
	public function getMethod(bool $upper = false): string
	{
		return ($upper) ? strtoupper($this->method) : strtolower($this->method);
	}

	//--------------------------------------------------------------------

	/**
	 * Fires the actual cURL request.
	 *
	 * @param string $method
	 * @param string $url
	 *
	 * @return \CodeIgniter\HTTP\ResponseInterface
	 */
	public function send(string $method, string $url)
	{
		// Reset our curl options so we're on a fresh slate.
		$curl_options = [];

		if (! empty($this->config['query']) && is_array($this->config['query']))
		{
			// This is likely too naive a solution.
			// Should look into handling when $url already
			// has query vars on it.
			$url .= '?' . http_build_query($this->config['query']);
			unset($this->config['query']);
		}

		$curl_options[CURLOPT_URL]            = $url;
		$curl_options[CURLOPT_RETURNTRANSFER] = true;
		$curl_options[CURLOPT_HEADER]         = true;
		$curl_options[CURLOPT_FRESH_CONNECT]  = true;
		// Disable @file uploads in post data.
		$curl_options[CURLOPT_SAFE_UPLOAD] = true;

		$curl_options = $this->setCURLOptions($curl_options, $this->config);
		$curl_options = $this->applyMethod($method, $curl_options);
		$curl_options = $this->applyRequestHeaders($curl_options);

		// Do we need to delay this request?
		if ($this->delay > 0)
		{
			sleep($this->delay);
		}

		$output = $this->sendRequest($curl_options);

		if (strpos($output, 'HTTP/1.1 100 Continue') === 0)
		{
			$output = substr($output, strpos($output, "\r\n\r\n") + 4);
		}

		// Split out our headers and body
		$break = strpos($output, "\r\n\r\n");

		if ($break !== false)
		{
			// Our headers
			$headers = explode("\n", substr($output, 0, $break));

			$this->setResponseHeaders($headers);

			// Our body
			$body = substr($output, $break + 4);
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
	 *
	 * @return array
	 */
	protected function applyRequestHeaders(array $curl_options = []): array
	{
		if (empty($this->headers))
		{
			$this->populateHeaders();
			// Otherwise, it will corrupt the request
			$this->removeHeader('Host');
			$this->removeHeader('Accept-Encoding');
		}

		$headers = $this->getHeaders();

		if (empty($headers))
		{
			return $curl_options;
		}

		$set = [];

		foreach ($headers as $name => $value)
		{
			$set[] = $name . ': ' . $this->getHeaderLine($name);
		}

		$curl_options[CURLOPT_HTTPHEADER] = $set;

		return $curl_options;
	}

	//--------------------------------------------------------------------

	/**
	 * Apply method
	 *
	 * @param string $method
	 * @param array  $curl_options
	 *
	 * @return array
	 */
	protected function applyMethod(string $method, array $curl_options): array
	{
		$method = strtoupper($method);

		$this->method                        = $method;
		$curl_options[CURLOPT_CUSTOMREQUEST] = $method;

		$size = strlen($this->body);

		// Have content?
		if ($size === null || $size > 0)
		{
			return $this->applyBody($curl_options);
		}

		if ($method === 'PUT' || $method === 'POST')
		{
			// See http://tools.ietf.org/html/rfc7230#section-3.3.2
			if (is_null($this->getHeader('content-length')) && ! isset($this->config['multipart']))
			{
				$this->setHeader('Content-Length', '0');
			}
		}
		else if ($method === 'HEAD')
		{
			$curl_options[CURLOPT_NOBODY] = 1;
		}

		return $curl_options;
	}

	//--------------------------------------------------------------------

	/**
	 * Apply body
	 *
	 * @param array $curl_options
	 *
	 * @return array
	 */
	protected function applyBody(array $curl_options = []): array
	{
		if (! empty($this->body))
		{
			$curl_options[CURLOPT_POSTFIELDS] = (string) $this->getBody();
		}

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
				$value = substr($header, $pos + 1);

				$this->response->setHeader($title, $value);
			}
			else if (strpos($header, 'HTTP') === 0)
			{
				preg_match('#^HTTP\/([12]\.[01]) ([0-9]+) (.+)#', $header, $matches);

				if (isset($matches[1]))
				{
					$this->response->setProtocolVersion($matches[1]);
				}

				if (isset($matches[2]))
				{
					$this->response->setStatusCode($matches[2], $matches[3] ?? null);
				}
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Set CURL options
	 *
	 * @param  array $curl_options
	 * @param  array $config
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	protected function setCURLOptions(array $curl_options = [], array $config = [])
	{
		// Auth Headers
		if (! empty($config['auth']))
		{
			$curl_options[CURLOPT_USERPWD] = $config['auth'][0] . ':' . $config['auth'][1];

			if (! empty($config['auth'][2]) && strtolower($config['auth'][2]) === 'digest')
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
				$cert                                = $cert[0];
			}

			if (! is_file($cert))
			{
				throw HTTPException::forSSLCertNotFound($cert);
			}

			$curl_options[CURLOPT_SSLCERT] = $cert;
		}

		// SSL Verification
		if (isset($config['verify']))
		{
			if (is_string($config['verify']))
			{
				$file = realpath($config['ssl_key']);

				if (! $file)
				{
					throw HTTPException::forInvalidSSLKey($config['ssl_key']);
				}

				$curl_options[CURLOPT_CAINFO]         = $file;
				$curl_options[CURLOPT_SSL_VERIFYPEER] = 1;
			}
			else if (is_bool($config['verify']))
			{
				$curl_options[CURLOPT_SSL_VERIFYPEER] = $config['verify'];
			}
		}

		// Debug
		if ($config['debug'])
		{
			$curl_options[CURLOPT_VERBOSE] = 1;
			$curl_options[CURLOPT_STDERR]  = is_string($config['debug']) ? fopen($config['debug'], 'a+') : fopen('php://stderr', 'w');
		}

		// Decode Content
		if (! empty($config['decode_content']))
		{
			$accept = $this->getHeaderLine('Accept-Encoding');

			if ($accept)
			{
				$curl_options[CURLOPT_ENCODING] = $accept;
			}
			else
			{
				$curl_options[CURLOPT_ENCODING]   = '';
				$curl_options[CURLOPT_HTTPHEADER] = 'Accept-Encoding';
			}
		}

		// Allow Redirects
		if (array_key_exists('allow_redirects', $config))
		{
			$settings = $this->redirectDefaults;

			if (is_array($config['allow_redirects']))
			{
				$settings = array_merge($settings, $config['allow_redirects']);
			}

			if ($config['allow_redirects'] === false)
			{
				$curl_options[CURLOPT_FOLLOWLOCATION] = 0;
			}
			else
			{
				$curl_options[CURLOPT_FOLLOWLOCATION] = 1;
				$curl_options[CURLOPT_MAXREDIRS]      = $settings['max'];

				if ($settings['strict'] === true)
				{
					$curl_options[CURLOPT_POSTREDIR] = 1 | 2 | 4;
				}

				$protocols = 0;
				foreach ($settings['protocols'] as $proto)
				{
					$protocols += constant('CURLPROTO_' . strtoupper($proto));
				}

				$curl_options[CURLOPT_REDIR_PROTOCOLS] = $protocols;
			}
		}

		// Timeout
		$curl_options[CURLOPT_TIMEOUT_MS] = (float) $config['timeout'] * 1000;

		// Connection Timeout
		$curl_options[CURLOPT_CONNECTTIMEOUT_MS] = (float) $config['connect_timeout'] * 1000;

		// Post Data - application/x-www-form-urlencoded
		if (! empty($config['form_params']) && is_array($config['form_params']))
		{
			$postFields                       = http_build_query($config['form_params']);
			$curl_options[CURLOPT_POSTFIELDS] = $postFields;

			// Ensure content-length is set, since CURL doesn't seem to
			// calculate it when HTTPHEADER is set.
			$this->setHeader('Content-Length', (string) strlen($postFields));
			$this->setHeader('Content-Type', 'application/x-www-form-urlencoded');
		}

		// Post Data - multipart/form-data
		if (! empty($config['multipart']) && is_array($config['multipart']))
		{
			// setting the POSTFIELDS option automatically sets multipart
			$curl_options[CURLOPT_POSTFIELDS] = $config['multipart'];
		}

		// HTTP Errors
		$curl_options[CURLOPT_FAILONERROR] = array_key_exists('http_errors', $config) ? (bool) $config['http_errors'] : true;

		// JSON
		if (isset($config['json']))
		{
			// Will be set as the body in `applyBody()`
			$json = json_encode($config['json']);
			$this->setBody($json);
			$this->setHeader('Content-Type', 'application/json');
			$this->setHeader('Content-Length', (string) strlen($json));
		}

		// version
		if (! empty($config['version']))
		{
			if ($config['version'] === 1.0)
			{
				$curl_options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_0;
			}
			else if ($config['version'] === 1.1)
			{
				$curl_options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
			}
		}

		// Cookie
		if (isset($config['cookie']))
		{
			$curl_options[CURLOPT_COOKIEJAR]  = $config['cookie'];
			$curl_options[CURLOPT_COOKIEFILE] = $config['cookie'];
		}

		return $curl_options;
	}

	//--------------------------------------------------------------------

	/**
	 * Does the actual work of initializing cURL, setting the options,
	 * and grabbing the output.
	 *
	 * @codeCoverageIgnore
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

		if ($output === false)
		{
			throw HTTPException::forCurlError(curl_errno($ch), curl_error($ch));
		}

		curl_close($ch);

		return $output;
	}

	//--------------------------------------------------------------------
}
