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

class SiteURIFactory
{
    /**
     * @var array Superglobal SERVER array
     */
    private array $server;

    private App $appConfig;

    /**
     * @param array $server Superglobal $_SERVER array
     */
    public function __construct(array $server, App $appConfig)
    {
        $this->server    = $server;
        $this->appConfig = $appConfig;
    }

    /**
     * Create the current URI object from superglobals.
     *
     * This method updates superglobal $_SERVER and $_GET.
     */
    public function createFromGlobals(): SiteURI
    {
        $routePath = $this->detectRoutePath();

        return $this->createURIFromRoutePath($routePath);
    }

    /**
     * Create the SiteURI object from URI string.
     *
     * @internal Used for testing purposes only.
     */
    public function createFromString(string $uri): SiteURI
    {
        // Validate URI
        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw HTTPException::forUnableToParseURI($uri);
        }

        $parts = parse_url($uri);

        if ($parts === false) {
            throw HTTPException::forUnableToParseURI($uri);
        }

        $query = $fragment = '';
        if (isset($parts['query'])) {
            $query = '?' . $parts['query'];
        }
        if (isset($parts['fragment'])) {
            $fragment = '#' . $parts['fragment'];
        }

        $relativePath = $parts['path'] . $query . $fragment;
        $host         = $this->getValidHost($parts['host']);

        return new SiteURI($this->appConfig, $relativePath, $host, $parts['scheme']);
    }

    /**
     * Detects the current URI path relative to baseURL based on the URIProtocol
     * Config setting.
     *
     * @param string $protocol URIProtocol
     *
     * @return string The route path
     *
     * @internal Used for testing purposes only.
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
        $path  = $parts['path'] ?? '';

        // Strip the SCRIPT_NAME path from the URI
        if (
            $path !== '' && isset($this->server['SCRIPT_NAME'][0])
            && pathinfo($this->server['SCRIPT_NAME'], PATHINFO_EXTENSION) === 'php'
        ) {
            // Compare each segment, dropping them until there is no match
            $segments = $keep = explode('/', $path);

            foreach (explode('/', $this->server['SCRIPT_NAME']) as $i => $segment) {
                // If these segments are not the same then we're done
                if (! isset($segments[$i]) || $segment !== $segments[$i]) {
                    break;
                }

                array_shift($keep);
            }

            $path = implode('/', $keep);
        }

        // This section ensures that even on servers that require the URI to
        // contain the query string (Nginx) a correct URI is found, and also
        // fixes the QUERY_STRING Server var and $_GET array.
        if (trim($path, '/') === '' && strncmp($query, '/', 1) === 0) {
            $parts    = explode('?', $query, 2);
            $path     = $parts[0];
            $newQuery = $query[1] ?? '';

            $this->server['QUERY_STRING'] = $newQuery;
            $this->updateServer('QUERY_STRING', $newQuery);
        } else {
            $this->server['QUERY_STRING'] = $query;
            $this->updateServer('QUERY_STRING', $query);
        }

        // Update our global GET for values likely to have been changed
        parse_str($this->server['QUERY_STRING'], $get);
        $this->updateGetArray($get);

        return URI::removeDotSegments($path);
    }

    private function updateServer(string $key, string $value): void
    {
        $_SERVER[$key] = $value;
    }

    private function updateGetArray(array $array): void
    {
        $_GET = $array;
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
        $query = $this->server['QUERY_STRING'] ?? @getenv('QUERY_STRING');

        if (trim($query, '/') === '') {
            return '/';
        }

        if (strncmp($query, '/', 1) === 0) {
            $parts    = explode('?', $query, 2);
            $path     = $parts[0];
            $newQuery = $parts[1] ?? '';

            $this->server['QUERY_STRING'] = $newQuery;
            $this->updateServer('QUERY_STRING', $newQuery);
        } else {
            $path = $query;
        }

        // Update our global GET for values likely to have been changed
        parse_str($this->server['QUERY_STRING'], $get);
        $this->updateGetArray($get);

        return URI::removeDotSegments($path);
    }

    /**
     * Create current URI object.
     *
     * @param string $routePath URI path relative to baseURL
     */
    private function createURIFromRoutePath(string $routePath): SiteURI
    {
        $query = $this->server['QUERY_STRING'] ?? '';

        $relativePath = $query !== '' ? $routePath . '?' . $query : $routePath;

        return new SiteURI($this->appConfig, $relativePath, $this->getHost());
    }

    /**
     * @return string|null The current hostname. Returns null if no valid host.
     */
    private function getHost(): ?string
    {
        $httpHostPort = $this->server['HTTP_HOST'] ?? null;

        if ($httpHostPort !== null) {
            [$httpHost] = explode(':', $httpHostPort, 2);

            return $this->getValidHost($httpHost);
        }

        return null;
    }

    /**
     * @return string|null The valid hostname. Returns null if not valid.
     */
    private function getValidHost(string $host): ?string
    {
        if (in_array($host, $this->appConfig->allowedHostnames, true)) {
            return $host;
        }

        return null;
    }
}
