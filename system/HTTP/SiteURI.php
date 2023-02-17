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

use BadMethodCallException;
use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use Config\App;

/**
 * URI for the application site
 */
class SiteURI extends URI
{
    /**
     * The baseURL.
     */
    private string $baseURL;

    /**
     * The Index File.
     */
    private string $indexPage;

    /**
     * List of URI segments in baseURL and indexPage.
     *
     * If the URI is "http://localhost:8888/ci431/public/index.php/test?a=b",
     * and the baseUR is "http://localhost:8888/ci431/public/", then:
     *   $baseSegments = [
     *       0 => 'ci431',
     *       1 => 'public',
     *       2 => 'index.php',
     *   ];
     */
    private array $baseSegments;

    /**
     * List of URI segments after indexPage.
     *
     * The word "URI Segments" originally means only the URI path part relative
     * to the baseURL.
     *
     * If the URI is "http://localhost:8888/ci431/public/index.php/test?a=b",
     * and the baseUR is "http://localhost:8888/ci431/public/", then:
     *   $segments = [
     *       0 => 'test',
     *   ];
     *
     * @var array
     *
     * @deprecated This property will be private.
     */
    protected $segments;

    /**
     * URI path relative to baseURL.
     *
     * If the baseURL contains sub folders, this value will be different from
     * the current URI path.
     */
    private string $routePath;

    /**
     * @param string $relativePath URI path relative to baseURL. May include
     *                             queries or fragments.
     * @param string $host         Optional hostname. If it is not in $allowedHostnames,
     *                             just be ignored.
     * @param string $scheme       Optional scheme. 'http' or 'https'.
     * @phpstan-param 'http'|'https'|'' $scheme
     */
    public function __construct(App $configApp, string $relativePath = '', string $host = '', string $scheme = '')
    {
        $this->baseURL   = $this->normalizeBaseURL($configApp);
        $this->indexPage = $configApp->indexPage;

        $this->setBaseSegments();

        // Check for an index page
        $indexPage = '';
        if ($configApp->indexPage !== '') {
            $indexPage = $configApp->indexPage;

            // Check if we need a separator
            if ($relativePath !== '' && $relativePath[0] !== '/' && $relativePath[0] !== '?') {
                $indexPage .= '/';
            }
        }

        $relativePath = URI::removeDotSegments($relativePath);

        $tempUri = $this->baseURL . $indexPage . $relativePath;
        $uri     = new URI($tempUri);

        // Update scheme
        if ($scheme !== '') {
            $uri->setScheme($scheme);
        } elseif ($configApp->forceGlobalSecureRequests) {
            $uri->setScheme('https');
        }

        // Update host
        if ($host !== '' && $this->checkHost($host, $configApp->allowedHostnames)) {
            $uri->setHost($host);
        }

        $parts = parse_url((string) $uri);
        if ($parts === false) {
            throw HTTPException::forUnableToParseURI($uri);
        }
        $this->applyParts($parts);

        // Set routePath
        $parts     = explode('?', $relativePath);
        $routePath = $parts[0];
        $this->setRoutePath($routePath);
    }

    private function checkHost(string $host, array $allowedHostnames): bool
    {
        return in_array($host, $allowedHostnames, true);
    }

    private function normalizeBaseURL(App $configApp): string
    {
        // It's possible the user forgot a trailing slash on their
        // baseURL, so let's help them out.
        $baseURL = rtrim($configApp->baseURL, '/ ') . '/';

        // Validate baseURL
        if (filter_var($baseURL, FILTER_VALIDATE_URL) === false) {
            throw new ConfigException(
                'Config\App::$baseURL is invalid.'
            );
        }

        return $baseURL;
    }

    /**
     * Sets baseSegments.
     */
    private function setBaseSegments(): void
    {
        $basePath           = (new URI($this->baseURL))->getPath();
        $this->baseSegments = $this->convertToSegments($basePath);

        if ($this->indexPage) {
            $this->baseSegments[] = $this->indexPage;
        }
    }

    /**
     * @deprecated
     */
    public function setURI(?string $uri = null)
    {
        throw new BadMethodCallException('Cannot use this method.');
    }

    /**
     * Returns the baseURL.
     *
     * @interal
     */
    public function getBaseURL(): string
    {
        return $this->baseURL;
    }

    /**
     * Returns the URI path relative to baseURL.
     *
     * @return string The Route path.
     */
    public function getRoutePath(): string
    {
        return $this->routePath;
    }

    /**
     * Returns the URI segments of the path as an array.
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    /**
     * Returns the value of a specific segment of the URI path relative to baseURL.
     *
     * @param int    $number  Segment number
     * @param string $default Default value
     *
     * @return string The value of the segment. If no segment is found,
     *                throws HTTPException
     */
    public function getSegment(int $number, string $default = ''): string
    {
        if ($number < 1) {
            throw HTTPException::forURISegmentOutOfRange($number);
        }

        if ($number > count($this->segments) && ! $this->silent) {
            throw HTTPException::forURISegmentOutOfRange($number);
        }

        // The segment should treat the array as 1-based for the user
        // but we still have to deal with a zero-based array.
        $number--;

        return $this->segments[$number] ?? $default;
    }

    /**
     * Set the value of a specific segment of the URI path relative to baseURL.
     * Allows to set only existing segments or add new one.
     *
     * @param int    $number The segment number. Starting with 1.
     * @param string $value  The segment value.
     *
     * @return $this
     */
    public function setSegment(int $number, $value)
    {
        if ($number < 1) {
            throw HTTPException::forURISegmentOutOfRange($number);
        }

        if ($number > count($this->segments) + 1) {
            if ($this->silent) {
                return $this;
            }

            throw HTTPException::forURISegmentOutOfRange($number);
        }

        // The segment should treat the array as 1-based for the user,
        // but we still have to deal with a zero-based array.
        $number--;

        $this->segments[$number] = $value;

        $this->refreshPath();

        return $this;
    }

    /**
     * Returns the total number of segments.
     */
    public function getTotalSegments(): int
    {
        return count($this->segments);
    }

    /**
     * Formats the URI as a string.
     */
    public function __toString(): string
    {
        return static::createURIString(
            $this->getScheme(),
            $this->getAuthority(),
            $this->getPath(), // Absolute URIs should use a "/" for an empty path
            $this->getQuery(),
            $this->getFragment()
        );
    }

    /**
     * Sets the route path (and segments).
     *
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->setRoutePath($path);

        return $this;
    }

    /**
     * Sets the route path (and segments).
     */
    private function setRoutePath(string $path): void
    {
        $this->routePath = $this->filterPath($path);

        $this->segments = $this->convertToSegments($this->routePath);

        $this->refreshPath();
    }

    /**
     * Converts path to segments
     */
    private function convertToSegments(string $path): array
    {
        $tempPath = trim($path, '/');

        return ($tempPath === '') ? [] : explode('/', $tempPath);
    }

    /**
     * Sets the path portion of the URI based on segments.
     *
     * @return $this
     *
     * @deprecated This method will be private.
     */
    public function refreshPath()
    {
        $allSegments = array_merge($this->baseSegments, $this->segments);
        $this->path  = '/' . $this->filterPath(implode('/', $allSegments));

        $this->routePath = $this->filterPath(implode('/', $this->segments));

        if ($this->routePath === '') {
            $this->routePath = '/';

            if ($this->indexPage !== '') {
                $this->path .= '/';
            }
        }

        return $this;
    }

    /**
     * Saves our parts from a parse_url() call.
     */
    protected function applyParts(array $parts)
    {
        if (! empty($parts['host'])) {
            $this->host = $parts['host'];
        }
        if (! empty($parts['user'])) {
            $this->user = $parts['user'];
        }
        if (isset($parts['path']) && $parts['path'] !== '') {
            $this->path = $this->filterPath($parts['path']);
        }
        if (! empty($parts['query'])) {
            $this->setQuery($parts['query']);
        }
        if (! empty($parts['fragment'])) {
            $this->fragment = $parts['fragment'];
        }

        // Scheme
        if (isset($parts['scheme'])) {
            $this->setScheme(rtrim($parts['scheme'], ':/'));
        } else {
            $this->setScheme('http');
        }

        // Port
        if (isset($parts['port']) && $parts['port'] !== null) {
            // Valid port numbers are enforced by earlier parse_url() or setPort()
            $this->port = $parts['port'];
        }

        if (isset($parts['pass'])) {
            $this->password = $parts['pass'];
        }
    }
}
