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
use CodeIgniter\HTTP\Files\FileCollection;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\App;
use Config\Services;
use InvalidArgumentException;
use Locale;

/**
 * Class IncomingRequest
 *
 * Represents an incoming, getServer-side HTTP request.
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
 * - Deserialized body binds (generally from $_POST)
 */
class IncomingRequest extends Request
{
    /**
     * Enable CSRF flag
     *
     * Enables a CSRF cookie token to be set.
     * Set automatically based on Config setting.
     *
     * @var bool
     *
     * @deprecated Not used
     */
    protected $enableCSRF = false;

    /**
     * The URI for this request.
     *
     * Note: This WILL NOT match the actual URL in the browser since for
     * everything this cares about (and the router, etc) is the portion
     * AFTER the script name. So, if hosted in a sub-folder this will
     * appear different than actual URL. If you need that use getPath().
     *
     * @TODO should be protected. Use getUri() instead.
     *
     * @var URI
     */
    public $uri;

    /**
     * The detected path (relative to SCRIPT_NAME).
     *
     * Note: current_url() uses this to build its URI,
     * so this becomes the source for the "current URL"
     * when working with the share request instance.
     *
     * @var string|null
     */
    protected $path;

    /**
     * File collection
     *
     * @var FileCollection|null
     */
    protected $files;

    /**
     * Negotiator
     *
     * @var Negotiate|null
     */
    protected $negotiator;

    /**
     * The default Locale this request
     * should operate under.
     *
     * @var string
     */
    protected $defaultLocale;

    /**
     * The current locale of the application.
     * Default value is set in Config\App.php
     *
     * @var string
     */
    protected $locale;

    /**
     * Stores the valid locale codes.
     *
     * @var array
     */
    protected $validLocales = [];

    /**
     * Configuration settings.
     *
     * @var App
     */
    public $config;

    /**
     * Holds the old data from a redirect.
     *
     * @var array
     */
    protected $oldInput = [];

    /**
     * The user agent this request is from.
     *
     * @var UserAgent
     */
    protected $userAgent;

    /**
     * Constructor
     *
     * @param App         $config
     * @param URI         $uri
     * @param string|null $body
     * @param UserAgent   $userAgent
     */
    public function __construct($config, ?URI $uri = null, $body = 'php://input', ?UserAgent $userAgent = null)
    {
        if (empty($uri) || empty($userAgent)) {
            throw new InvalidArgumentException('You must supply the parameters: uri, userAgent.');
        }

        // Get our body from php://input
        if ($body === 'php://input') {
            $body = file_get_contents('php://input');
        }

        $this->config       = $config;
        $this->uri          = $uri;
        $this->body         = ! empty($body) ? $body : null;
        $this->userAgent    = $userAgent;
        $this->validLocales = $config->supportedLocales;

        parent::__construct($config);

        $this->populateHeaders();
        $this->detectURI($config->uriProtocol, $config->baseURL);
        $this->detectLocale($config);
    }

    /**
     * Handles setting up the locale, perhaps auto-detecting through
     * content negotiation.
     *
     * @param App $config
     */
    public function detectLocale($config)
    {
        $this->locale = $this->defaultLocale = $config->defaultLocale;

        if (! $config->negotiateLocale) {
            return;
        }

        $this->setLocale($this->negotiate('language', $config->supportedLocales));
    }

    /**
     * Sets up our URI object based on the information we have. This is
     * either provided by the user in the baseURL Config setting, or
     * determined from the environment as needed.
     */
    protected function detectURI(string $protocol, string $baseURL)
    {
        // Passing the config is unnecessary but left for legacy purposes
        $config          = clone $this->config;
        $config->baseURL = $baseURL;

        $this->setPath($this->detectPath($protocol), $config);
    }

    /**
     * Detects the relative path based on
     * the URIProtocol Config setting.
     */
    public function detectPath(string $protocol = ''): string
    {
        if (empty($protocol)) {
            $protocol = 'REQUEST_URI';
        }

        switch ($protocol) {
            case 'REQUEST_URI':
                $this->path = $this->parseRequestURI();
                break;

            case 'QUERY_STRING':
                $this->path = $this->parseQueryString();
                break;

            case 'PATH_INFO':
            default:
                $this->path = $this->fetchGlobal('server', $protocol) ?? $this->parseRequestURI();
                break;
        }

        return $this->path;
    }

    /**
     * Will parse the REQUEST_URI and automatically detect the URI from it,
     * fixing the query string if necessary.
     *
     * @return string The URI it found.
     */
    protected function parseRequestURI(): string
    {
        if (! isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
            return '';
        }

        // parse_url() returns false if no host is present, but the path or query string
        // contains a colon followed by a number. So we attach a dummy host since
        // REQUEST_URI does not include the host. This allows us to parse out the query string and path.
        $parts = parse_url('http://dummy' . $_SERVER['REQUEST_URI']);
        $query = $parts['query'] ?? '';
        $uri   = $parts['path'] ?? '';

        // Strip the SCRIPT_NAME path from the URI
        if ($uri !== '' && isset($_SERVER['SCRIPT_NAME'][0]) && pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_EXTENSION) === 'php') {
            // Compare each segment, dropping them until there is no match
            $segments = $keep = explode('/', $uri);

            foreach (explode('/', $_SERVER['SCRIPT_NAME']) as $i => $segment) {
                // If these segments are not the same then we're done
                if (! isset($segments[$i]) || $segment !== $segments[$i]) {
                    break;
                }

                array_shift($keep);
            }

            $uri = implode('/', $keep);
        }

        // This section ensures that even on servers that require the URI to contain the query string (Nginx) a correct
        // URI is found, and also fixes the QUERY_STRING getServer var and $_GET array.
        if (trim($uri, '/') === '' && strncmp($query, '/', 1) === 0) {
            $query                   = explode('?', $query, 2);
            $uri                     = $query[0];
            $_SERVER['QUERY_STRING'] = $query[1] ?? '';
        } else {
            $_SERVER['QUERY_STRING'] = $query;
        }

        // Update our globals for values likely to been have changed
        parse_str($_SERVER['QUERY_STRING'], $_GET);
        $this->populateGlobals('server');
        $this->populateGlobals('get');

        $uri = URI::removeDotSegments($uri);

        return ($uri === '/' || $uri === '') ? '/' : ltrim($uri, '/');
    }

    /**
     * Parse QUERY_STRING
     *
     * Will parse QUERY_STRING and automatically detect the URI from it.
     */
    protected function parseQueryString(): string
    {
        $uri = $_SERVER['QUERY_STRING'] ?? @getenv('QUERY_STRING');

        if (trim($uri, '/') === '') {
            return '';
        }

        if (strncmp($uri, '/', 1) === 0) {
            $uri                     = explode('?', $uri, 2);
            $_SERVER['QUERY_STRING'] = $uri[1] ?? '';
            $uri                     = $uri[0];
        }

        // Update our globals for values likely to been have changed
        parse_str($_SERVER['QUERY_STRING'], $_GET);
        $this->populateGlobals('server');
        $this->populateGlobals('get');

        $uri = URI::removeDotSegments($uri);

        return ($uri === '/' || $uri === '') ? '/' : ltrim($uri, '/');
    }

    /**
     * Provides a convenient way to work with the Negotiate class
     * for content negotiation.
     */
    public function negotiate(string $type, array $supported, bool $strictMatch = false): string
    {
        if ($this->negotiator === null) {
            $this->negotiator = Services::negotiator($this, true);
        }

        switch (strtolower($type)) {
            case 'media':
                return $this->negotiator->media($supported, $strictMatch);

            case 'charset':
                return $this->negotiator->charset($supported);

            case 'encoding':
                return $this->negotiator->encoding($supported);

            case 'language':
                return $this->negotiator->language($supported);
        }

        throw HTTPException::forInvalidNegotiationType($type);
    }

    /**
     * Determines if this request was made from the command line (CLI).
     */
    public function isCLI(): bool
    {
        return false;
    }

    /**
     * Test to see if a request contains the HTTP_X_REQUESTED_WITH header.
     */
    public function isAJAX(): bool
    {
        return $this->hasHeader('X-Requested-With') && strtolower($this->header('X-Requested-With')->getValue()) === 'xmlhttprequest';
    }

    /**
     * Attempts to detect if the current connection is secure through
     * a few different methods.
     */
    public function isSecure(): bool
    {
        if (! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        if ($this->hasHeader('X-Forwarded-Proto') && $this->header('X-Forwarded-Proto')->getValue() === 'https') {
            return true;
        }

        return $this->hasHeader('Front-End-Https') && ! empty($this->header('Front-End-Https')->getValue()) && strtolower($this->header('Front-End-Https')->getValue()) !== 'off';
    }

    /**
     * Sets the relative path and updates the URI object.
     * Note: Since current_url() accesses the shared request
     * instance, this can be used to change the "current URL"
     * for testing.
     *
     * @param string $path   URI path relative to SCRIPT_NAME
     * @param App    $config Optional alternate config to use
     *
     * @return $this
     */
    public function setPath(string $path, ?App $config = null)
    {
        $this->path = $path;
        $this->uri->setPath($path);

        $config ??= $this->config;

        // It's possible the user forgot a trailing slash on their
        // baseURL, so let's help them out.
        $baseURL = $config->baseURL === '' ? $config->baseURL : rtrim($config->baseURL, '/ ') . '/';

        // Based on our baseURL provided by the developer
        // set our current domain name, scheme
        if ($baseURL !== '') {
            $this->uri->setScheme(parse_url($baseURL, PHP_URL_SCHEME));
            $this->uri->setHost(parse_url($baseURL, PHP_URL_HOST));
            $this->uri->setPort(parse_url($baseURL, PHP_URL_PORT));

            // Ensure we have any query vars
            $this->uri->setQuery($_SERVER['QUERY_STRING'] ?? '');

            // Check if the baseURL scheme needs to be coerced into its secure version
            if ($config->forceGlobalSecureRequests && $this->uri->getScheme() === 'http') {
                $this->uri->setScheme('https');
            }
        } elseif (! is_cli()) {
            // @codeCoverageIgnoreStart
            exit('You have an empty or invalid base URL. The baseURL value must be set in Config\App.php, or through the .env file.');
            // @codeCoverageIgnoreEnd
        }

        return $this;
    }

    /**
     * Returns the path relative to SCRIPT_NAME,
     * running detection as necessary.
     */
    public function getPath(): string
    {
        if ($this->path === null) {
            $this->detectPath($this->config->uriProtocol);
        }

        return $this->path;
    }

    /**
     * Sets the locale string for this request.
     *
     * @return IncomingRequest
     */
    public function setLocale(string $locale)
    {
        // If it's not a valid locale, set it
        // to the default locale for the site.
        if (! in_array($locale, $this->validLocales, true)) {
            $locale = $this->defaultLocale;
        }

        $this->locale = $locale;
        Locale::setDefault($locale);

        return $this;
    }

    /**
     * Gets the current locale, with a fallback to the default
     * locale if none is set.
     */
    public function getLocale(): string
    {
        return $this->locale ?? $this->defaultLocale;
    }

    /**
     * Returns the default locale as set in Config\App.php
     */
    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    /**
     * Fetch an item from JSON input stream with fallback to $_REQUEST object. This is the simplest way
     * to grab data from the request object and can be used in lieu of the
     * other get* methods in most cases.
     *
     * @param array|string|null $index
     * @param int|null          $filter Filter constant
     * @param mixed             $flags
     *
     * @return mixed
     */
    public function getVar($index = null, $filter = null, $flags = null)
    {
        if (strpos($this->getHeaderLine('Content-Type'), 'application/json') !== false && $this->body !== null) {
            if ($index === null) {
                return $this->getJSON();
            }

            if (is_array($index)) {
                $output = [];

                foreach ($index as $key) {
                    $output[$key] = $this->getJsonVar($key, false, $filter, $flags);
                }

                return $output;
            }

            return $this->getJsonVar($index, false, $filter, $flags);
        }

        return $this->fetchGlobal('request', $index, $filter, $flags);
    }

    /**
     * A convenience method that grabs the raw input stream and decodes
     * the JSON into an array.
     *
     * If $assoc == true, then all objects in the response will be converted
     * to associative arrays.
     *
     * @param bool $assoc   Whether to return objects as associative arrays
     * @param int  $depth   How many levels deep to decode
     * @param int  $options Bitmask of options
     *
     * @see http://php.net/manual/en/function.json-decode.php
     *
     * @return mixed
     */
    public function getJSON(bool $assoc = false, int $depth = 512, int $options = 0)
    {
        return json_decode($this->body ?? '', $assoc, $depth, $options);
    }

    /**
     * Get a specific variable from a JSON input stream
     *
     * @param string         $index  The variable that you want which can use dot syntax for getting specific values.
     * @param bool           $assoc  If true, return the result as an associative array.
     * @param int|null       $filter Filter Constant
     * @param array|int|null $flags  Option
     *
     * @return mixed
     */
    public function getJsonVar(string $index, bool $assoc = false, ?int $filter = null, $flags = null)
    {
        helper('array');

        $json = $this->getJSON(true);
        if (! is_array($json)) {
            return null;
        }
        $data = dot_array_search($index, $json);

        if ($data === null) {
            return null;
        }

        if (! is_array($data)) {
            $filter ??= FILTER_DEFAULT;
            $flags = is_array($flags) ? $flags : (is_numeric($flags) ? (int) $flags : 0);

            return filter_var($data, $filter, $flags);
        }

        if (! $assoc) {
            return json_decode(json_encode($data));
        }

        return $data;
    }

    /**
     * A convenience method that grabs the raw input stream(send method in PUT, PATCH, DELETE) and decodes
     * the String into an array.
     *
     * @return mixed
     */
    public function getRawInput()
    {
        parse_str($this->body ?? '', $output);

        return $output;
    }

    /**
     * Fetch an item from GET data.
     *
     * @param array|string|null $index  Index for item to fetch from $_GET.
     * @param int|null          $filter A filter name to apply.
     * @param mixed|null        $flags
     *
     * @return mixed
     */
    public function getGet($index = null, $filter = null, $flags = null)
    {
        return $this->fetchGlobal('get', $index, $filter, $flags);
    }

    /**
     * Fetch an item from POST.
     *
     * @param array|string|null $index  Index for item to fetch from $_POST.
     * @param int|null          $filter A filter name to apply
     * @param mixed             $flags
     *
     * @return mixed
     */
    public function getPost($index = null, $filter = null, $flags = null)
    {
        return $this->fetchGlobal('post', $index, $filter, $flags);
    }

    /**
     * Fetch an item from POST data with fallback to GET.
     *
     * @param array|string|null $index  Index for item to fetch from $_POST or $_GET
     * @param int|null          $filter A filter name to apply
     * @param mixed             $flags
     *
     * @return mixed
     */
    public function getPostGet($index = null, $filter = null, $flags = null)
    {
        // Use $_POST directly here, since filter_has_var only
        // checks the initial POST data, not anything that might
        // have been added since.
        return isset($_POST[$index]) ? $this->getPost($index, $filter, $flags) : (isset($_GET[$index]) ? $this->getGet($index, $filter, $flags) : $this->getPost($index, $filter, $flags));
    }

    /**
     * Fetch an item from GET data with fallback to POST.
     *
     * @param array|string|null $index  Index for item to be fetched from $_GET or $_POST
     * @param int|null          $filter A filter name to apply
     * @param mixed             $flags
     *
     * @return mixed
     */
    public function getGetPost($index = null, $filter = null, $flags = null)
    {
        // Use $_GET directly here, since filter_has_var only
        // checks the initial GET data, not anything that might
        // have been added since.
        return isset($_GET[$index]) ? $this->getGet($index, $filter, $flags) : (isset($_POST[$index]) ? $this->getPost($index, $filter, $flags) : $this->getGet($index, $filter, $flags));
    }

    /**
     * Fetch an item from the COOKIE array.
     *
     * @param array|string|null $index  Index for item to be fetched from $_COOKIE
     * @param int|null          $filter A filter name to be applied
     * @param mixed             $flags
     *
     * @return mixed
     */
    public function getCookie($index = null, $filter = null, $flags = null)
    {
        return $this->fetchGlobal('cookie', $index, $filter, $flags);
    }

    /**
     * Fetch the user agent string
     *
     * @return UserAgent
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Attempts to get old Input data that has been flashed to the session
     * with redirect_with_input(). It first checks for the data in the old
     * POST data, then the old GET data and finally check for dot arrays
     *
     * @return mixed
     */
    public function getOldInput(string $key)
    {
        // If the session hasn't been started, or no
        // data was previously saved, we're done.
        if (empty($_SESSION['_ci_old_input'])) {
            return null;
        }

        // Check for the value in the POST array first.
        if (isset($_SESSION['_ci_old_input']['post'][$key])) {
            return $_SESSION['_ci_old_input']['post'][$key];
        }

        // Next check in the GET array.
        if (isset($_SESSION['_ci_old_input']['get'][$key])) {
            return $_SESSION['_ci_old_input']['get'][$key];
        }

        helper('array');

        // Check for an array value in POST.
        if (isset($_SESSION['_ci_old_input']['post'])) {
            $value = dot_array_search($key, $_SESSION['_ci_old_input']['post']);
            if ($value !== null) {
                return $value;
            }
        }

        // Check for an array value in GET.
        if (isset($_SESSION['_ci_old_input']['get'])) {
            $value = dot_array_search($key, $_SESSION['_ci_old_input']['get']);
            if ($value !== null) {
                return $value;
            }
        }

        // requested session key not found
        return null;
    }

    /**
     * Returns an array of all files that have been uploaded with this
     * request. Each file is represented by an UploadedFile instance.
     */
    public function getFiles(): array
    {
        if ($this->files === null) {
            $this->files = new FileCollection();
        }

        return $this->files->all(); // return all files
    }

    /**
     * Verify if a file exist, by the name of the input field used to upload it, in the collection
     * of uploaded files and if is have been uploaded with multiple option.
     *
     * @return array|null
     */
    public function getFileMultiple(string $fileID)
    {
        if ($this->files === null) {
            $this->files = new FileCollection();
        }

        return $this->files->getFileMultiple($fileID);
    }

    /**
     * Retrieves a single file by the name of the input field used
     * to upload it.
     *
     * @return UploadedFile|null
     */
    public function getFile(string $fileID)
    {
        if ($this->files === null) {
            $this->files = new FileCollection();
        }

        return $this->files->getFile($fileID);
    }

    /**
     * Remove relative directory (../) and multi slashes (///)
     *
     * Do some final cleaning of the URI and return it, currently only used in static::_parse_request_uri()
     *
     * @deprecated Use URI::removeDotSegments() directly
     */
    protected function removeRelativeDirectory(string $uri): string
    {
        $uri = URI::removeDotSegments($uri);

        return $uri === '/' ? $uri : ltrim($uri, '/');
    }
}
