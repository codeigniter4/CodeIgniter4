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

use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\Validation\FormatRules;
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
        if ($this->ipAddress) {
            return $this->ipAddress;
        }

        $ipValidator = [
            new FormatRules(),
            'valid_ip',
        ];

        $proxyIPs = $this->config->proxyIPs;

        if (! empty($proxyIPs) && (! is_array($proxyIPs) || is_int(array_key_first($proxyIPs)))) {
            throw new ConfigException(
                'You must set an array with Proxy IP address key and HTTP header name value in Config\App::$proxyIPs.'
            );
        }

        $this->ipAddress = $this->getServer('REMOTE_ADDR');

        // If this is a CLI request, $this->ipAddress is null.
        if ($this->ipAddress === null) {
            return $this->ipAddress = '0.0.0.0';
        }

        // @TODO Extract all this IP address logic to another class.
        foreach ($proxyIPs as $proxyIP => $header) {
            // Check if we have an IP address or a subnet
            if (! str_contains($proxyIP, '/')) {
                // An IP address (and not a subnet) is specified.
                // We can compare right away.
                if ($proxyIP === $this->ipAddress) {
                    $spoof = $this->getClientIP($header);

                    if ($spoof !== null) {
                        $this->ipAddress = $spoof;
                        break;
                    }
                }

                continue;
            }

            // We have a subnet ... now the heavy lifting begins
            if (! isset($separator)) {
                $separator = $ipValidator($this->ipAddress, 'ipv6') ? ':' : '.';
            }

            // If the proxy entry doesn't match the IP protocol - skip it
            if (! str_contains($proxyIP, $separator)) {
                continue;
            }

            // Convert the REMOTE_ADDR IP address to binary, if needed
            if (! isset($ip, $sprintf)) {
                if ($separator === ':') {
                    // Make sure we're having the "full" IPv6 format
                    $ip = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($this->ipAddress, ':')), $this->ipAddress));

                    for ($j = 0; $j < 8; $j++) {
                        $ip[$j] = intval($ip[$j], 16);
                    }

                    $sprintf = '%016b%016b%016b%016b%016b%016b%016b%016b';
                } else {
                    $ip      = explode('.', $this->ipAddress);
                    $sprintf = '%08b%08b%08b%08b';
                }

                $ip = vsprintf($sprintf, $ip);
            }

            // Split the netmask length off the network address
            sscanf($proxyIP, '%[^/]/%d', $netaddr, $masklen);

            // Again, an IPv6 address is most likely in a compressed form
            if ($separator === ':') {
                $netaddr = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($netaddr, ':')), $netaddr));

                for ($i = 0; $i < 8; $i++) {
                    $netaddr[$i] = intval($netaddr[$i], 16);
                }
            } else {
                $netaddr = explode('.', $netaddr);
            }

            // Convert to binary and finally compare
            if (strncmp($ip, vsprintf($sprintf, $netaddr), $masklen) === 0) {
                $spoof = $this->getClientIP($header);

                if ($spoof !== null) {
                    $this->ipAddress = $spoof;
                    break;
                }
            }
        }

        if (! $ipValidator($this->ipAddress)) {
            return $this->ipAddress = '0.0.0.0';
        }

        return $this->ipAddress;
    }

    /**
     * Gets the client IP address from the HTTP header.
     */
    private function getClientIP(string $header): ?string
    {
        $ipValidator = [
            new FormatRules(),
            'valid_ip',
        ];
        $spoof     = null;
        $headerObj = $this->header($header);

        if ($headerObj !== null) {
            $spoof = $headerObj->getValue();

            // Some proxies typically list the whole chain of IP
            // addresses through which the client has reached us.
            // e.g. client_ip, proxy_ip1, proxy_ip2, etc.
            sscanf($spoof, '%[^,]', $spoof);

            if (! $ipValidator($spoof)) {
                $spoof = null;
            }
        }

        return $spoof;
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
     * @param         string                                   $name  Supergrlobal name (lowercase)
     * @phpstan-param 'get'|'post'|'request'|'cookie'|'server' $name
     * @param         mixed                                    $value
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
     * @param         string                                   $name   Supergrlobal name (lowercase)
     * @phpstan-param 'get'|'post'|'request'|'cookie'|'server' $name
     * @param         array|string|null                        $index
     * @param         int|null                                 $filter Filter constant
     * @param         array|int|null                           $flags  Options
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
        if (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1) {
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
            array_walk_recursive($value, static function (&$val) use ($filter, $flags) {
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
     * @param         string                                   $name Superglobal name (lowercase)
     * @phpstan-param 'get'|'post'|'request'|'cookie'|'server' $name
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
