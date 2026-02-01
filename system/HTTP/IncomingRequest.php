<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\Files\FileCollection;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\App;
use Config\Services;
use Locale;
use stdClass;

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
 * - Deserialized body binds (generally from $_POST)
 *
 * @see \CodeIgniter\HTTP\IncomingRequestTest
 */
class IncomingRequest extends Request
{
    /**
     * The URI for this request.
     *
     * Note: This WILL NOT match the actual URL in the browser since for
     * everything this cares about (and the router, etc) is the portion
     * AFTER the baseURL. So, if hosted in a sub-folder this will
     * appear different than actual URI path. If you need that use getPath().
     *
     * @var URI
     */
    protected $uri;

    /**
     * The detected URI path (relative to the baseURL).
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
     * Default value is set in app/Config/App.php
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
     * @param string|null $body
     */
    public function __construct($config, ?URI $uri = null, $body = 'php://input', ?UserAgent $userAgent = null)
    {
        if (! $uri instanceof URI || ! $userAgent instanceof UserAgent) {
            throw new InvalidArgumentException('You must supply the parameters: uri, userAgent.');
        }

        $this->populateHeaders();

        if (
            $body === 'php://input'
            // php://input is not available with enctype="multipart/form-data".
            // See https://www.php.net/manual/en/wrappers.php.php#wrappers.php.input
            && ! str_contains($this->getHeaderLine('Content-Type'), 'multipart/form-data')
            && (int) $this->getHeaderLine('Content-Length') <= $this->getPostMaxSize()
        ) {
            // Get our body from php://input
            $body = file_get_contents('php://input');
        }

        // If file_get_contents() returns false or empty string, set null.
        if ($body === false || $body === '') {
            $body = null;
        }

        $this->uri          = $uri;
        $this->body         = $body;
        $this->userAgent    = $userAgent;
        $this->validLocales = $config->supportedLocales;

        parent::__construct($config);

        if ($uri instanceof SiteURI) {
            $this->setPath($uri->getRoutePath());
        } else {
            $this->setPath($uri->getPath());
        }

        $this->detectLocale($config);
    }

    private function getPostMaxSize(): int
    {
        $postMaxSize = ini_get('post_max_size');

        return match (strtoupper(substr($postMaxSize, -1))) {
            'G'     => (int) str_replace('G', '', $postMaxSize) * 1024 ** 3,
            'M'     => (int) str_replace('M', '', $postMaxSize) * 1024 ** 2,
            'K'     => (int) str_replace('K', '', $postMaxSize) * 1024,
            default => (int) $postMaxSize,
        };
    }

    /**
     * Handles setting up the locale, perhaps auto-detecting through
     * content negotiation.
     *
     * @param App $config
     *
     * @return void
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
     * Provides a convenient way to work with the Negotiate class
     * for content negotiation.
     */
    public function negotiate(string $type, array $supported, bool $strictMatch = false): string
    {
        if ($this->negotiator === null) {
            $this->negotiator = Services::negotiator($this, true);
        }

        return match (strtolower($type)) {
            'media'    => $this->negotiator->media($supported, $strictMatch),
            'charset'  => $this->negotiator->charset($supported),
            'encoding' => $this->negotiator->encoding($supported),
            'language' => $this->negotiator->language($supported),
            default    => throw HTTPException::forInvalidNegotiationType($type),
        };
    }

    /**
     * Checks this request type.
     */
    public function is(string $type): bool
    {
        $valueUpper = strtoupper($type);

        $httpMethods = Method::all();

        if (in_array($valueUpper, $httpMethods, true)) {
            return $this->getMethod() === $valueUpper;
        }

        if ($valueUpper === 'JSON') {
            return str_contains($this->getHeaderLine('Content-Type'), 'application/json');
        }

        if ($valueUpper === 'AJAX') {
            return $this->isAJAX();
        }

        throw new InvalidArgumentException('Unknown type: ' . $type);
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
        return $this->hasHeader('X-Requested-With')
            && strtolower($this->header('X-Requested-With')->getValue()) === 'xmlhttprequest';
    }

    /**
     * Attempts to detect if the current connection is secure through
     * a few different methods.
     */
    public function isSecure(): bool
    {
        $https = service('superglobals')->server('HTTPS');

        if ($https !== null && strtolower($https) !== 'off') {
            return true;
        }

        if ($this->hasHeader('X-Forwarded-Proto') && $this->header('X-Forwarded-Proto')->getValue() === 'https') {
            return true;
        }

        return $this->hasHeader('Front-End-Https') && ! empty($this->header('Front-End-Https')->getValue()) && strtolower($this->header('Front-End-Https')->getValue()) !== 'off';
    }

    /**
     * Sets the URI path relative to baseURL.
     *
     * Note: Since current_url() accesses the shared request
     * instance, this can be used to change the "current URL"
     * for testing.
     *
     * @param string $path URI path relative to baseURL
     *
     * @return $this
     */
    private function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Returns the URI path relative to baseURL,
     * running detection as necessary.
     */
    public function getPath(): string
    {
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
     * Set the valid locales.
     *
     * @return $this
     */
    public function setValidLocales(array $locales)
    {
        $this->validLocales = $locales;

        return $this;
    }

    /**
     * Gets the current locale, with a fallback to the default
     * locale if none is set.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Returns the default locale as set in app/Config/App.php
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
     * @param array|int|null    $flags
     *
     * @return array|bool|float|int|stdClass|string|null
     */
    public function getVar($index = null, $filter = null, $flags = null)
    {
        if (
            str_contains($this->getHeaderLine('Content-Type'), 'application/json')
            && $this->body !== null
        ) {
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
     * @return array|bool|float|int|stdClass|null
     *
     * @throws HTTPException When the body is invalid as JSON.
     */
    public function getJSON(bool $assoc = false, int $depth = 512, int $options = 0)
    {
        if ($this->body === null) {
            return null;
        }

        $result = json_decode($this->body, $assoc, $depth, $options);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw HTTPException::forInvalidJSON(json_last_error_msg());
        }

        return $result;
    }

    /**
     * Get a specific variable from a JSON input stream
     *
     * @param array|string|null $index  The variable that you want which can use dot syntax for getting specific values.
     * @param bool              $assoc  If true, return the result as an associative array.
     * @param int|null          $filter Filter Constant
     * @param array|int|null    $flags  Option
     *
     * @return array|bool|float|int|stdClass|string|null
     */
    public function getJsonVar($index = null, bool $assoc = false, ?int $filter = null, $flags = null)
    {
        helper('array');

        $data = $this->getJSON(true);
        if (! is_array($data)) {
            return null;
        }

        if (is_string($index)) {
            $data = dot_array_search($index, $data);
        } elseif (is_array($index)) {
            $result = [];

            foreach ($index as $key) {
                $result[$key] = dot_array_search($key, $data);
            }

            [$data, $result] = [$result, null];
        }

        if ($data === null) {
            return null;
        }

        $filter ??= FILTER_UNSAFE_RAW;
        $flags = is_array($flags) ? $flags : (is_numeric($flags) ? (int) $flags : 0);

        if ($filter !== FILTER_UNSAFE_RAW
            || (
                (is_numeric($flags) && $flags !== 0)
                || is_array($flags) && $flags !== []
            )
        ) {
            if (is_array($data)) {
                // Iterate over array and append filter and flags
                array_walk_recursive($data, static function (&$val) use ($filter, $flags): void {
                    $valType = gettype($val);
                    $val     = filter_var($val, $filter, $flags);

                    if (in_array($valType, ['int', 'integer', 'float', 'double', 'bool', 'boolean'], true) && $val !== false) {
                        settype($val, $valType);
                    }
                });
            } else {
                $dataType = gettype($data);
                $data     = filter_var($data, $filter, $flags);

                if (in_array($dataType, ['int', 'integer', 'float', 'double', 'bool', 'boolean'], true) && $data !== false) {
                    settype($data, $dataType);
                }
            }
        }

        if (! $assoc) {
            if (is_array($index)) {
                foreach ($data as &$val) {
                    $val = is_array($val) ? json_decode(json_encode($val)) : $val;
                }

                return $data;
            }

            return json_decode(json_encode($data));
        }

        return $data;
    }

    /**
     * A convenience method that grabs the raw input stream(send method in PUT, PATCH, DELETE) and decodes
     * the String into an array.
     *
     * @return array
     */
    public function getRawInput()
    {
        parse_str($this->body ?? '', $output);

        return $output;
    }

    /**
     * Gets a specific variable from raw input stream (send method in PUT, PATCH, DELETE).
     *
     * @param array|string|null $index  The variable that you want which can use dot syntax for getting specific values.
     * @param int|null          $filter Filter Constant
     * @param array|int|null    $flags  Option
     *
     * @return array|bool|float|int|object|string|null
     */
    public function getRawInputVar($index = null, ?int $filter = null, $flags = null)
    {
        helper('array');

        parse_str($this->body ?? '', $output);

        if (is_string($index)) {
            $output = dot_array_search($index, $output);
        } elseif (is_array($index)) {
            $data = [];

            foreach ($index as $key) {
                $data[$key] = dot_array_search($key, $output);
            }

            [$output, $data] = [$data, null];
        }

        $filter ??= FILTER_UNSAFE_RAW;
        $flags = is_array($flags) ? $flags : (is_numeric($flags) ? (int) $flags : 0);

        if (is_array($output)
            && (
                $filter !== FILTER_UNSAFE_RAW
                || (
                    (is_numeric($flags) && $flags !== 0)
                    || is_array($flags) && $flags !== []
                )
            )
        ) {
            // Iterate over array and append filter and flags
            array_walk_recursive($output, static function (&$val) use ($filter, $flags): void {
                $val = filter_var($val, $filter, $flags);
            });

            return $output;
        }

        if (is_string($output)) {
            return filter_var($output, $filter, $flags);
        }

        return $output;
    }

    /**
     * Fetch an item from GET data.
     *
     * @param array|string|null $index  Index for item to fetch from $_GET.
     * @param int|null          $filter A filter name to apply.
     * @param array|int|null    $flags
     *
     * @return array|bool|float|int|object|string|null
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
     * @param array|int|null    $flags
     *
     * @return array|bool|float|int|object|string|null
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
     * @param array|int|null    $flags
     *
     * @return array|bool|float|int|object|string|null
     */
    public function getPostGet($index = null, $filter = null, $flags = null)
    {
        if ($index === null) {
            return array_merge($this->getGet($index, $filter, $flags), $this->getPost($index, $filter, $flags));
        }

        // Use $_POST directly here, since filter_has_var only
        // checks the initial POST data, not anything that might
        // have been added since.
        return service('superglobals')->post($index) !== null
            ? $this->getPost($index, $filter, $flags)
            : (service('superglobals')->get($index) !== null ? $this->getGet($index, $filter, $flags) : $this->getPost($index, $filter, $flags));
    }

    /**
     * Fetch an item from GET data with fallback to POST.
     *
     * @param array|string|null $index  Index for item to be fetched from $_GET or $_POST
     * @param int|null          $filter A filter name to apply
     * @param array|int|null    $flags
     *
     * @return array|bool|float|int|object|string|null
     */
    public function getGetPost($index = null, $filter = null, $flags = null)
    {
        if ($index === null) {
            return array_merge($this->getPost($index, $filter, $flags), $this->getGet($index, $filter, $flags));
        }

        // Use $_GET directly here, since filter_has_var only
        // checks the initial GET data, not anything that might
        // have been added since.
        return service('superglobals')->get($index) !== null
            ? $this->getGet($index, $filter, $flags)
            : (service('superglobals')->post($index) !== null ? $this->getPost($index, $filter, $flags) : $this->getGet($index, $filter, $flags));
    }

    /**
     * Fetch an item from the COOKIE array.
     *
     * @param array|string|null $index  Index for item to be fetched from $_COOKIE
     * @param int|null          $filter A filter name to be applied
     * @param array|int|null    $flags
     *
     * @return array|bool|float|int|object|string|null
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
     * @return array|string|null
     */
    public function getOldInput(string $key)
    {
        // If the session hasn't been started, we're done.
        if (! isset($_SESSION)) {
            return null;
        }

        // Get previously saved in session
        $old = session('_ci_old_input');

        // If no data was previously saved, we're done.
        if ($old === null) {
            return null;
        }

        // Check for the value in the POST array first.
        if (isset($old['post'][$key])) {
            return $old['post'][$key];
        }

        // Next check in the GET array.
        if (isset($old['get'][$key])) {
            return $old['get'][$key];
        }

        helper('array');

        // Check for an array value in POST.
        if (isset($old['post'])) {
            $value = dot_array_search($key, $old['post']);
            if ($value !== null) {
                return $value;
            }
        }

        // Check for an array value in GET.
        if (isset($old['get'])) {
            $value = dot_array_search($key, $old['get']);
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
}
