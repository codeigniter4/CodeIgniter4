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

use Config\App;

/**
 * Request Trait
 *
 * Additional methods to make a PSR-7 Request class
 * compliant with the framework's own RequestInterface.
 *
 * @see https://github.com/php-fig/http-message/blob/master/src/RequestInterface.php
 */
trait RequestTrait
{
    /**
     * Configuration settings.
     *
     * @var App
     */
    protected $config;

    /**
     * IP address of the current user.
     *
     * @var string
     *
     * @deprecated Will become private in a future release
     */
    protected $ipAddress = '';

    /**
     * Stores values we've retrieved from PHP globals.
     *
     * @var array{get?: array, post?: array, request?: array, cookie?: array, server?: array}
     */
    protected $globals = [];

    /**
     * Gets the user's IP address.
     *
     * @return string IP address if it can be detected.
     *                If the IP address is not a valid IP address,
     *                then will return '0.0.0.0'.
     */
    public function getIPAddress(): string
    {
        if ($this->ipAddress !== '') {
            return $this->ipAddress;
        }

        $remoteAddr = $this->getServer('REMOTE_ADDR');

        // If this is a CLI request, $remoteAddr is null.
        if ($remoteAddr === null) {
            return $this->ipAddress = '0.0.0.0';
        }

        $proxyIPs = $this->config->proxyIPs;

        // Use the IPAddressDetector to handle the complex IP detection logic
        $detector = new IPAddressDetector();
        
        $this->ipAddress = $detector->detect(
            $remoteAddr,
            $proxyIPs,
            function (string $headerName): ?string {
                $headerObj = $this->header($headerName);
                
                return $headerObj !== null ? $headerObj->getValue() : null;
            }
        );

        return $this->ipAddress;
    }



    /**
     * Fetch an item from the $_SERVER array.
     *
     * @param array|string|null $index  Index for item to be fetched from $_SERVER
     * @param int|null          $filter A filter name to be applied
     * @param array|int|null    $flags
     *
     * @return mixed
     */
    public function getServer($index = null, $filter = null, $flags = null)
    {
        return $this->fetchGlobal('server', $index, $filter, $flags);
    }

    /**
     * Fetch an item from the $_ENV array.
     *
     * @param array|string|null $index  Index for item to be fetched from $_ENV
     * @param int|null          $filter A filter name to be applied
     * @param array|int|null    $flags
     *
     * @return mixed
     *
     * @deprecated 4.4.4 This method does not work from the beginning. Use `env()`.
     */
    public function getEnv($index = null, $filter = null, $flags = null)
    {
        // @phpstan-ignore-next-line
        return $this->fetchGlobal('env', $index, $filter, $flags);
    }

    /**
     * Allows manually setting the value of PHP global, like $_GET, $_POST, etc.
     *
     * @param 'cookie'|'get'|'post'|'request'|'server' $name  Superglobal name (lowercase)
     * @param mixed                                    $value
     *
     * @return $this
     */
    public function setGlobal(string $name, $value)
    {
        $this->globals[$name] = $value;

        return $this;
    }

    /**
     * Fetches one or more items from a global, like cookies, get, post, etc.
     * Can optionally filter the input when you retrieve it by passing in
     * a filter.
     *
     * If $type is an array, it must conform to the input allowed by the
     * filter_input_array method.
     *
     * http://php.net/manual/en/filter.filters.sanitize.php
     *
     * @param 'cookie'|'get'|'post'|'request'|'server' $name   Superglobal name (lowercase)
     * @param array|int|string|null                    $index
     * @param int|null                                 $filter Filter constant
     * @param array|int|null                           $flags  Options
     *
     * @return array|bool|float|int|object|string|null
     */
    public function fetchGlobal(string $name, $index = null, ?int $filter = null, $flags = null)
    {
        if (! isset($this->globals[$name])) {
            $this->populateGlobals($name);
        }

        // Null filters cause null values to return.
        $filter ??= FILTER_DEFAULT;
        $flags = is_array($flags) ? $flags : (is_numeric($flags) ? (int) $flags : 0);

        // Return all values when $index is null
        if ($index === null) {
            $values = [];

            foreach ($this->globals[$name] as $key => $value) {
                $values[$key] = is_array($value)
                    ? $this->fetchGlobal($name, $key, $filter, $flags)
                    : filter_var($value, $filter, $flags);
            }

            return $values;
        }

        // allow fetching multiple keys at once
        if (is_array($index)) {
            $output = [];

            foreach ($index as $key) {
                $output[$key] = $this->fetchGlobal($name, $key, $filter, $flags);
            }

            return $output;
        }

        // Does the index contain array notation?
        if (is_string($index) && ($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1) {
            $value = $this->globals[$name];

            for ($i = 0; $i < $count; $i++) {
                $key = trim($matches[0][$i], '[]');

                if ($key === '') { // Empty notation will return the value as array
                    break;
                }

                if (isset($value[$key])) {
                    $value = $value[$key];
                } else {
                    return null;
                }
            }
        }

        if (! isset($value)) {
            $value = $this->globals[$name][$index] ?? null;
        }

        if (is_array($value)
            && (
                $filter !== FILTER_DEFAULT
                || (
                    (is_numeric($flags) && $flags !== 0)
                    || is_array($flags) && $flags !== []
                )
            )
        ) {
            // Iterate over array and append filter and flags
            array_walk_recursive($value, static function (&$val) use ($filter, $flags): void {
                $val = filter_var($val, $filter, $flags);
            });

            return $value;
        }

        // Cannot filter these types of data automatically...
        if (is_array($value) || is_object($value) || $value === null) {
            return $value;
        }

        return filter_var($value, $filter, $flags);
    }

    /**
     * Saves a copy of the current state of one of several PHP globals,
     * so we can retrieve them later.
     *
     * @param 'cookie'|'get'|'post'|'request'|'server' $name Superglobal name (lowercase)
     *
     * @return void
     */
    protected function populateGlobals(string $name)
    {
        if (! isset($this->globals[$name])) {
            $this->globals[$name] = [];
        }

        // Don't populate ENV as it might contain
        // sensitive data that we don't want to get logged.
        switch ($name) {
            case 'get':
                $this->globals['get'] = $_GET;
                break;

            case 'post':
                $this->globals['post'] = $_POST;
                break;

            case 'request':
                $this->globals['request'] = $_REQUEST;
                break;

            case 'cookie':
                $this->globals['cookie'] = $_COOKIE;
                break;

            case 'server':
                $this->globals['server'] = $_SERVER;
                break;
        }
    }
}
