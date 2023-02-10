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
     * Detects the current URI path relative to baseURL based on the URIProtocol
     * Config setting.
     *
     * @param string $protocol URIProtocol
     *
     * @return string The route path
     */
    public function detectRoutePath(string $protocol = ''): string
    {
        if ($protocol === '') {
            $protocol = $this->appConfig->uriProtocol;
        }

        switch ($protocol) {
            case 'REQUEST_URI':
                $routePath = $this->parseRequestURI();
                break;

            case 'QUERY_STRING':
                $routePath = $this->parseQueryString();
                break;

            case 'PATH_INFO':
            default:
                $routePath = $this->server[$protocol] ?? $this->parseRequestURI();
                break;
        }

        return ($routePath === '/' || $routePath === '') ? '/' : ltrim($routePath, '/');
    }

    /**
     * Will parse the REQUEST_URI and automatically detect the URI from it,
     * fixing the query string if necessary.
     *
     * This method updates superglobal $_SERVER and $_GET.
     *
     * @return string The route path (before normalization).
     */
    private function parseRequestURI(): string
    {
        if (! isset($this->server['REQUEST_URI'], $this->server['SCRIPT_NAME'])) {
            return '';
        }

        // parse_url() returns false if no host is present, but the path or query
        // string contains a colon followed by a number. So we attach a dummy
        // host since REQUEST_URI does not include the host. This allows us to
        // parse out the query string and path.
        $parts = parse_url('http://dummy' . $this->server['REQUEST_URI']);
        $query = $parts['query'] ?? '';
        $uri   = $parts['path'] ?? '';

        // Strip the SCRIPT_NAME path from the URI
        if (
            $uri !== '' && isset($this->server['SCRIPT_NAME'][0])
            && pathinfo($this->server['SCRIPT_NAME'], PATHINFO_EXTENSION) === 'php'
        ) {
            // Compare each segment, dropping them until there is no match
            $segments = $keep = explode('/', $uri);

            foreach (explode('/', $this->server['SCRIPT_NAME']) as $i => $segment) {
                // If these segments are not the same then we're done
                if (! isset($segments[$i]) || $segment !== $segments[$i]) {
                    break;
                }

                array_shift($keep);
            }

            $uri = implode('/', $keep);
        }

        // This section ensures that even on servers that require the URI to
        // contain the query string (Nginx) a correct URI is found, and also
        // fixes the QUERY_STRING Server var and $_GET array.
        if (trim($uri, '/') === '' && strncmp($query, '/', 1) === 0) {
            $query                        = explode('?', $query, 2);
            $uri                          = $query[0];
            $this->server['QUERY_STRING'] = $query[1] ?? '';
        } else {
            $this->server['QUERY_STRING'] = $query;
        }

        // Update our globals for values likely to have been changed
        parse_str($this->server['QUERY_STRING'], $this->get);

        return URI::removeDotSegments($uri);
    }

    /**
     * Will parse QUERY_STRING and automatically detect the URI from it.
     *
     * This method updates superglobal $_SERVER and $_GET.
     *
     * @return string The route path (before normalization).
     */
    private function parseQueryString(): string
    {
        $uri = $this->server['QUERY_STRING'] ?? @getenv('QUERY_STRING');

        if (trim($uri, '/') === '') {
            return '/';
        }

        if (strncmp($uri, '/', 1) === 0) {
            $uri                          = explode('?', $uri, 2);
            $this->server['QUERY_STRING'] = $uri[1] ?? '';
            $uri                          = $uri[0];
        }

        // Update our globals for values likely to have been changed
        parse_str($this->server['QUERY_STRING'], $this->get);

        return URI::removeDotSegments($uri);
    }

    /**
     * Create current URI object.
     *
     * @param string $routePath URI path relative to baseURL
     */
    private function createURIFromRoutePath(string $routePath): URI
    {
        $config = $this->appConfig;

        // It's possible the user forgot a trailing slash on their
        // baseURL, so let's help them out.
        $baseURL = ($config->baseURL === '')
            ? $config->baseURL
            : rtrim($config->baseURL, '/ ') . '/';

        // Based on our baseURL and allowedHostnames provided by the developer
        // and HTTP_HOST, set our current domain name, scheme.
        if ($baseURL !== '') {
            $host = $this->determineHost($baseURL);

            // Set URI::$baseURL
            $uri            = new URI($baseURL);
            $currentBaseURL = (string) $uri->setHost($host);
            $uri->setBaseURL($currentBaseURL);

            $uri->setPath($routePath);

            $uri->setRoutePath($routePath);

            $uri->setScheme(parse_url($baseURL, PHP_URL_SCHEME));
            $uri->setHost($host);
            $uri->setPort(parse_url($baseURL, PHP_URL_PORT));

            // Ensure we have any query vars
            $uri->setQuery($this->server['QUERY_STRING'] ?? '');

            // Check if the scheme needs to be coerced into its secure version
            if ($config->forceGlobalSecureRequests && $uri->getScheme() === 'http') {
                $uri->setScheme('https');
            }

            return $uri;
        }
        if (! is_cli()) {
            // Do not change exit() to exception; Request is initialized before
            // setting the exception handler, so if an exception is raised, an
            // error will be displayed even if in the production environment.
            // @codeCoverageIgnoreStart
            exit('You have an empty or invalid baseURL. The baseURL value must be set in app/Config/App.php, or through the .env file.');
            // @codeCoverageIgnoreEnd
        }

        return new URI();
    }

    /**
     * @return string The current hostname.
     */
    private function determineHost(string $baseURL): string
    {
        $host = parse_url($baseURL, PHP_URL_HOST);

        if (empty($this->appConfig->allowedHostnames)) {
            return $host;
        }

        // Update host if it is valid.
        $httpHostPort = $this->server['HTTP_HOST'] ?? null;
        if ($httpHostPort !== null) {
            [$httpHost] = explode(':', $httpHostPort, 2);

            if (in_array($httpHost, $this->appConfig->allowedHostnames, true)) {
                $host = $httpHost;
            }
        }

        return $host;
    }
}
