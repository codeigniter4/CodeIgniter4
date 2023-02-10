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

use Config\App;

class URIFactory
{
    /**
     * @var array Superglobal SERVER array
     */
    private array $server;

    /**
     * @var array Superglobal GET array
     */
    private array $get;

    private App $appConfig;

    /**
     * @param array $server Superglobal $_SERVER array
     * @param array $get    Superglobal $_GET array
     */
    public function __construct(array &$server, array &$get, App $appConfig)
    {
        $this->server    = &$server;
        $this->get       = &$get;
        $this->appConfig = $appConfig;
    }

    /**
     * Create the current URI object.
     *
     * This method updates superglobal $_SERVER and $_GET.
     */
    public function createCurrentURI(): URI
    {
        $routePath = $this->detectRoutePath();

        return $this->createURIFromRoutePath($routePath);
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
        if (
            $uri !== '' && isset($_SERVER['SCRIPT_NAME'][0])
            && pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_EXTENSION) === 'php'
        ) {
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
        // URI is found, and also fixes the QUERY_STRING Server var and $_GET array.
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
            return '/';
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
     * Sets the relative path and updates the URI object.
     *
     * Note: Since current_url() accesses the shared request
     * instance, this can be used to change the "current URL"
     * for testing.
     *
     * @param string   $path   URI path relative to baseURL
     * @param App|null $config Optional alternate config to use
     *
     * @return $this
     */
    public function setPath(string $path, ?App $config = null)
    {
        $this->path = $path;

        // @TODO remove this. The path of the URI object should be a full URI path,
        //      not a URI path relative to baseURL.
        $this->uri->setPath($path);

        $config ??= $this->config;

        // It's possible the user forgot a trailing slash on their
        // baseURL, so let's help them out.
        $baseURL = ($config->baseURL === '') ? $config->baseURL : rtrim($config->baseURL, '/ ') . '/';

        // Based on our baseURL and allowedHostnames provided by the developer
        // and HTTP_HOST, set our current domain name, scheme.
        if ($baseURL !== '') {
            $host = $this->determineHost($config, $baseURL);

            // Set URI::$baseURL
            $uri            = new URI($baseURL);
            $currentBaseURL = (string) $uri->setHost($host);
            $this->uri->setBaseURL($currentBaseURL);

            $this->uri->setScheme(parse_url($baseURL, PHP_URL_SCHEME));
            $this->uri->setHost($host);
            $this->uri->setPort(parse_url($baseURL, PHP_URL_PORT));

            // Ensure we have any query vars
            $this->uri->setQuery($_SERVER['QUERY_STRING'] ?? '');

            // Check if the scheme needs to be coerced into its secure version
            if ($config->forceGlobalSecureRequests && $this->uri->getScheme() === 'http') {
                $this->uri->setScheme('https');
            }
        } elseif (! is_cli()) {
            // Do not change exit() to exception; Request is initialized before
            // setting the exception handler, so if an exception is raised, an
            // error will be displayed even if in the production environment.
            // @codeCoverageIgnoreStart
            exit('You have an empty or invalid baseURL. The baseURL value must be set in app/Config/App.php, or through the .env file.');
            // @codeCoverageIgnoreEnd
        }

        return $this;
    }

    private function determineHost(App $config, string $baseURL): string
    {
        $host = parse_url($baseURL, PHP_URL_HOST);

        if (empty($config->allowedHostnames)) {
            return $host;
        }

        // Update host if it is valid.
        $httpHostPort = $this->getServer('HTTP_HOST');
        if ($httpHostPort !== null) {
            [$httpHost] = explode(':', $httpHostPort, 2);

            if (in_array($httpHost, $config->allowedHostnames, true)) {
                $host = $httpHost;
            }
        }

        return $host;
    }
}
