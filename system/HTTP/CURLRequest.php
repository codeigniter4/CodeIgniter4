<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use Config\App;
use Config\CURLRequest as ConfigCURLRequest;
use InvalidArgumentException;

/**
 * A lightweight HTTP client for sending synchronous HTTP requests via cURL.
 */
class CURLRequest extends Request
{
    /**
     * The response object associated with this request
     *
     * @var ResponseInterface|null
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
    protected $config;

    /**
     * The default setting values
     *
     * @var array
     */
    protected $defaultConfig = [
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

    /**
     * The default options from the constructor. Applied to all requests.
     */
    private array $defaultOptions;

    /**
     * Whether share options between requests or not.
     *
     * If true, all the options won't be reset between requests.
     * It may cause an error request with unnecessary headers.
     */
    private bool $shareOptions;

    /**
     * Takes an array of options to set the following possible class properties:
     *
     *  - baseURI
     *  - timeout
     *  - any other request options to use as defaults.
     *
     * @param ResponseInterface $response
     */
    public function __construct(App $config, URI $uri, ?ResponseInterface $response = null, array $options = [])
    {
        if (! function_exists('curl_version')) {
            throw HTTPException::forMissingCurl(); // @codeCoverageIgnore
        }

        parent::__construct($config);

        $this->response       = $response;
        $this->baseURI        = $uri->useRawQueryString();
        $this->defaultOptions = $options;

        /** @var ConfigCURLRequest|null $configCURLRequest */
        $configCURLRequest  = config('CURLRequest');
        $this->shareOptions = $configCURLRequest->shareOptions ?? true;

        $this->config = $this->defaultConfig;
        $this->parseOptions($options);
    }

    /**
     * Sends an HTTP request to the specified $url. If this is a relative
     * URL, it will be merged with $this->baseURI to form a complete URL.
     *
     * @param string $method
     */
    public function request($method, string $url, array $options = []): ResponseInterface
    {
        $this->parseOptions($options);

        $url = $this->prepareURL($url);

        $method = esc(strip_tags($method));

        $this->send($method, $url);

        if ($this->shareOptions === false) {
            $this->resetOptions();
        }

        return $this->response;
    }

    /**
     * Reset all options to default.
     */
    protected function resetOptions()
    {
        // Reset headers
        $this->headers   = [];
        $this->headerMap = [];

        // Reset body
        $this->body = null;

        // Reset configs
        $this->config = $this->defaultConfig;

        // Set the default options for next request
        $this->parseOptions($this->defaultOptions);
    }

    /**
     * Convenience method for sending a GET request.
     */
    public function get(string $url, array $options = []): ResponseInterface
    {
        return $this->request('get', $url, $options);
    }

    /**
     * Convenience method for sending a DELETE request.
     */
    public function delete(string $url, array $options = []): ResponseInterface
    {
        return $this->request('delete', $url, $options);
    }

    /**
     * Convenience method for sending a HEAD request.
     */
    public function head(string $url, array $options = []): ResponseInterface
    {
        return $this->request('head', $url, $options);
    }

    /**
     * Convenience method for sending an OPTIONS request.
     */
    public function options(string $url, array $options = []): ResponseInterface
    {
        return $this->request('options', $url, $options);
    }

    /**
     * Convenience method for sending a PATCH request.
     */
    public function patch(string $url, array $options = []): ResponseInterface
    {
        return $this->request('patch', $url, $options);
    }

    /**
     * Convenience method for sending a POST request.
     */
    public function post(string $url, array $options = []): ResponseInterface
    {
        return $this->request('post', $url, $options);
    }

    /**
     * Convenience method for sending a PUT request.
     */
    public function put(string $url, array $options = []): ResponseInterface
    {
        return $this->request('put', $url, $options);
    }

    /**
     * Set the HTTP Authentication.
     *
     * @param string $type basic or digest
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

    /**
     * Set form data to be sent.
     *
     * @param bool $multipart Set TRUE if you are sending CURLFiles
     *
     * @return $this
     */
    public function setForm(array $params, bool $multipart = false)
    {
        if ($multipart) {
            $this->config['multipart'] = $params;
        } else {
            $this->config['form_params'] = $params;
        }

        return $this;
    }

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

    /**
     * Sets the correct settings based on the options array
     * passed in.
     */
    protected function parseOptions(array $options)
    {
        if (array_key_exists('baseURI', $options)) {
            $this->baseURI = $this->baseURI->setURI($options['baseURI']);
            unset($options['baseURI']);
        }

        if (array_key_exists('headers', $options) && is_array($options['headers'])) {
            foreach ($options['headers'] as $name => $value) {
                $this->setHeader($name, $value);
            }

            unset($options['headers']);
        }

        if (array_key_exists('delay', $options)) {
            // Convert from the milliseconds passed in
            // to the seconds that sleep requires.
            $this->delay = (float) $options['delay'] / 1000;
            unset($options['delay']);
        }

        if (array_key_exists('body', $options)) {
            $this->setBody($options['body']);
            unset($options['body']);
        }

        foreach ($options as $key => $value) {
            $this->config[$key] = $value;
        }
    }

    /**
     * If the $url is a relative URL, will attempt to create
     * a full URL by prepending $this->baseURI to it.
     */
    protected function prepareURL(string $url): string
    {
        // If it's a full URI, then we have nothing to do here...
        if (strpos($url, '://') !== false) {
            return $url;
        }

        $uri = $this->baseURI->resolveRelativeURI($url);

        // Create the string instead of casting to prevent baseURL muddling
        return URI::createURIString($uri->getScheme(), $uri->getAuthority(), $uri->getPath(), $uri->getQuery(), $uri->getFragment());
    }

    /**
     * Get the request method. Overrides the Request class' method
     * since users expect a different answer here.
     *
     * @param bool|false $upper Whether to return in upper or lower case.
     */
    public function getMethod(bool $upper = false): string
    {
        return ($upper) ? strtoupper($this->method) : strtolower($this->method);
    }

    /**
     * Fires the actual cURL request.
     *
     * @return ResponseInterface
     */
    public function send(string $method, string $url)
    {
        // Reset our curl options so we're on a fresh slate.
        $curlOptions = [];

        if (! empty($this->config['query']) && is_array($this->config['query'])) {
            // This is likely too naive a solution.
            // Should look into handling when $url already
            // has query vars on it.
            $url .= '?' . http_build_query($this->config['query']);
            unset($this->config['query']);
        }

        $curlOptions[CURLOPT_URL]            = $url;
        $curlOptions[CURLOPT_RETURNTRANSFER] = true;
        $curlOptions[CURLOPT_HEADER]         = true;
        $curlOptions[CURLOPT_FRESH_CONNECT]  = true;
        // Disable @file uploads in post data.
        $curlOptions[CURLOPT_SAFE_UPLOAD] = true;

        $curlOptions = $this->setCURLOptions($curlOptions, $this->config);
        $curlOptions = $this->applyMethod($method, $curlOptions);
        $curlOptions = $this->applyRequestHeaders($curlOptions);

        // Do we need to delay this request?
        if ($this->delay > 0) {
            usleep((int) $this->delay * 1_000_000);
        }

        $output = $this->sendRequest($curlOptions);

        // Set the string we want to break our response from
        $breakString = "\r\n\r\n";

        if (strpos($output, 'HTTP/1.1 100 Continue') === 0) {
            $output = substr($output, strpos($output, $breakString) + 4);
        }

        // If request and response have Digest
        if (isset($this->config['auth'][2]) && $this->config['auth'][2] === 'digest' && strpos($output, 'WWW-Authenticate: Digest') !== false) {
            $output = substr($output, strpos($output, $breakString) + 4);
        }

        // Split out our headers and body
        $break = strpos($output, $breakString);

        if ($break !== false) {
            // Our headers
            $headers = explode("\n", substr($output, 0, $break));

            $this->setResponseHeaders($headers);

            // Our body
            $body = substr($output, $break + 4);
            $this->response->setBody($body);
        } else {
            $this->response->setBody($output);
        }

        return $this->response;
    }

    /**
     * Adds $this->headers to the cURL request.
     */
    protected function applyRequestHeaders(array $curlOptions = []): array
    {
        if (empty($this->headers)) {
            return $curlOptions;
        }

        $set = [];

        foreach (array_keys($this->headers) as $name) {
            $set[] = $name . ': ' . $this->getHeaderLine($name);
        }

        $curlOptions[CURLOPT_HTTPHEADER] = $set;

        return $curlOptions;
    }

    /**
     * Apply method
     */
    protected function applyMethod(string $method, array $curlOptions): array
    {
        $method = strtoupper($method);

        $this->method                       = $method;
        $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;

        $size = strlen($this->body ?? '');

        // Have content?
        if ($size > 0) {
            return $this->applyBody($curlOptions);
        }

        if ($method === 'PUT' || $method === 'POST') {
            // See http://tools.ietf.org/html/rfc7230#section-3.3.2
            if ($this->header('content-length') === null && ! isset($this->config['multipart'])) {
                $this->setHeader('Content-Length', '0');
            }
        } elseif ($method === 'HEAD') {
            $curlOptions[CURLOPT_NOBODY] = 1;
        }

        return $curlOptions;
    }

    /**
     * Apply body
     */
    protected function applyBody(array $curlOptions = []): array
    {
        if (! empty($this->body)) {
            $curlOptions[CURLOPT_POSTFIELDS] = (string) $this->getBody();
        }

        return $curlOptions;
    }

    /**
     * Parses the header retrieved from the cURL response into
     * our Response object.
     */
    protected function setResponseHeaders(array $headers = [])
    {
        foreach ($headers as $header) {
            if (($pos = strpos($header, ':')) !== false) {
                $title = substr($header, 0, $pos);
                $value = substr($header, $pos + 1);

                $this->response->setHeader($title, $value);
            } elseif (strpos($header, 'HTTP') === 0) {
                preg_match('#^HTTP\/([12](?:\.[01])?) (\d+) (.+)#', $header, $matches);

                if (isset($matches[1])) {
                    $this->response->setProtocolVersion($matches[1]);
                }

                if (isset($matches[2])) {
                    $this->response->setStatusCode((int) $matches[2], $matches[3] ?? null);
                }
            }
        }
    }

    /**
     * Set CURL options
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    protected function setCURLOptions(array $curlOptions = [], array $config = [])
    {
        // Auth Headers
        if (! empty($config['auth'])) {
            $curlOptions[CURLOPT_USERPWD] = $config['auth'][0] . ':' . $config['auth'][1];

            if (! empty($config['auth'][2]) && strtolower($config['auth'][2]) === 'digest') {
                $curlOptions[CURLOPT_HTTPAUTH] = CURLAUTH_DIGEST;
            } else {
                $curlOptions[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
            }
        }

        // Certificate
        if (! empty($config['cert'])) {
            $cert = $config['cert'];

            if (is_array($cert)) {
                $curlOptions[CURLOPT_SSLCERTPASSWD] = $cert[1];
                $cert                               = $cert[0];
            }

            if (! is_file($cert)) {
                throw HTTPException::forSSLCertNotFound($cert);
            }

            $curlOptions[CURLOPT_SSLCERT] = $cert;
        }

        // SSL Verification
        if (isset($config['verify'])) {
            if (is_string($config['verify'])) {
                $file = realpath($config['ssl_key']) ?: $config['ssl_key'];

                if (! is_file($file)) {
                    throw HTTPException::forInvalidSSLKey($config['ssl_key']);
                }

                $curlOptions[CURLOPT_CAINFO]         = $file;
                $curlOptions[CURLOPT_SSL_VERIFYPEER] = 1;
            } elseif (is_bool($config['verify'])) {
                $curlOptions[CURLOPT_SSL_VERIFYPEER] = $config['verify'];
            }
        }

        // Debug
        if ($config['debug']) {
            $curlOptions[CURLOPT_VERBOSE] = 1;
            $curlOptions[CURLOPT_STDERR]  = is_string($config['debug']) ? fopen($config['debug'], 'a+b') : fopen('php://stderr', 'wb');
        }

        // Decode Content
        if (! empty($config['decode_content'])) {
            $accept = $this->getHeaderLine('Accept-Encoding');

            if ($accept) {
                $curlOptions[CURLOPT_ENCODING] = $accept;
            } else {
                $curlOptions[CURLOPT_ENCODING]   = '';
                $curlOptions[CURLOPT_HTTPHEADER] = 'Accept-Encoding';
            }
        }

        // Allow Redirects
        if (array_key_exists('allow_redirects', $config)) {
            $settings = $this->redirectDefaults;

            if (is_array($config['allow_redirects'])) {
                $settings = array_merge($settings, $config['allow_redirects']);
            }

            if ($config['allow_redirects'] === false) {
                $curlOptions[CURLOPT_FOLLOWLOCATION] = 0;
            } else {
                $curlOptions[CURLOPT_FOLLOWLOCATION] = 1;
                $curlOptions[CURLOPT_MAXREDIRS]      = $settings['max'];

                if ($settings['strict'] === true) {
                    $curlOptions[CURLOPT_POSTREDIR] = 1 | 2 | 4;
                }

                $protocols = 0;

                foreach ($settings['protocols'] as $proto) {
                    $protocols += constant('CURLPROTO_' . strtoupper($proto));
                }

                $curlOptions[CURLOPT_REDIR_PROTOCOLS] = $protocols;
            }
        }

        // Timeout
        $curlOptions[CURLOPT_TIMEOUT_MS] = (float) $config['timeout'] * 1000;

        // Connection Timeout
        $curlOptions[CURLOPT_CONNECTTIMEOUT_MS] = (float) $config['connect_timeout'] * 1000;

        // Post Data - application/x-www-form-urlencoded
        if (! empty($config['form_params']) && is_array($config['form_params'])) {
            $postFields                      = http_build_query($config['form_params']);
            $curlOptions[CURLOPT_POSTFIELDS] = $postFields;

            // Ensure content-length is set, since CURL doesn't seem to
            // calculate it when HTTPHEADER is set.
            $this->setHeader('Content-Length', (string) strlen($postFields));
            $this->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        }

        // Post Data - multipart/form-data
        if (! empty($config['multipart']) && is_array($config['multipart'])) {
            // setting the POSTFIELDS option automatically sets multipart
            $curlOptions[CURLOPT_POSTFIELDS] = $config['multipart'];
        }

        // HTTP Errors
        $curlOptions[CURLOPT_FAILONERROR] = array_key_exists('http_errors', $config) ? (bool) $config['http_errors'] : true;

        // JSON
        if (isset($config['json'])) {
            // Will be set as the body in `applyBody()`
            $json = json_encode($config['json']);
            $this->setBody($json);
            $this->setHeader('Content-Type', 'application/json');
            $this->setHeader('Content-Length', (string) strlen($json));
        }

        // version
        if (! empty($config['version'])) {
            if ($config['version'] === 1.0) {
                $curlOptions[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_0;
            } elseif ($config['version'] === 1.1) {
                $curlOptions[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
            }
        }

        // Cookie
        if (isset($config['cookie'])) {
            $curlOptions[CURLOPT_COOKIEJAR]  = $config['cookie'];
            $curlOptions[CURLOPT_COOKIEFILE] = $config['cookie'];
        }

        // User Agent
        if (isset($config['user_agent'])) {
            $curlOptions[CURLOPT_USERAGENT] = $config['user_agent'];
        }

        return $curlOptions;
    }

    /**
     * Does the actual work of initializing cURL, setting the options,
     * and grabbing the output.
     *
     * @codeCoverageIgnore
     */
    protected function sendRequest(array $curlOptions = []): string
    {
        $ch = curl_init();

        curl_setopt_array($ch, $curlOptions);

        // Send the request and wait for a response.
        $output = curl_exec($ch);

        if ($output === false) {
            throw HTTPException::forCurlError((string) curl_errno($ch), curl_error($ch));
        }

        curl_close($ch);

        return $output;
    }
}
