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
     *
     * @deprecated 4.7.0 Use the Superglobals service instead
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

        $ipValidator = [
            new FormatRules(),
            'valid_ip',
        ];

        $proxyIPs = $this->config->proxyIPs;

        if (! empty($proxyIPs) && (! is_array($proxyIPs) || is_int(array_key_first($proxyIPs)))) {
            throw new ConfigException(
                'You must set an array with Proxy IP address key and HTTP header name value in Config\App::$proxyIPs.',
            );
        }

        $this->ipAddress = $this->getServer('REMOTE_ADDR');

        // If this is a CLI request, $this->ipAddress is null.
        if ($this->ipAddress === null) {
            return $this->ipAddress = '0.0.0.0';
        }

        // @TODO Extract all this IP address logic to another class.
        foreach ($proxyIPs as $proxyIP => $header) {
            if ($this->checkIPAgainstProxy($this->ipAddress, (string) $proxyIP)) {
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
     * Checks if the request comes from one of the trusted proxies
     * configured in Config\App::$proxyIPs.
     */
    protected function isFromTrustedProxy(): bool
    {
        $proxyIPs = $this->config->proxyIPs;

        if (! is_array($proxyIPs) || $proxyIPs === []) {
            return false;
        }

        $remoteAddr = $this->getServer('REMOTE_ADDR');

        if (! is_string($remoteAddr) || $remoteAddr === '') {
            return false;
        }

        foreach (array_keys($proxyIPs) as $proxyIP) {
            if ($this->checkIPAgainstProxy($remoteAddr, (string) $proxyIP)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the given IP address matches the trusted proxy entry,
     * which may be a single IP address or a subnet in CIDR notation.
     * Supports both IPv4 and IPv6.
     */
    private function checkIPAgainstProxy(string $ip, string $proxyIP): bool
    {
        $maskLength = null;

        if (str_contains($proxyIP, '/')) {
            [$proxyIP, $mask] = explode('/', $proxyIP, 2);

            if ($mask === '' || ! ctype_digit($mask)) {
                return false;
            }

            $maskLength = (int) $mask;
        }

        $binaryIP    = inet_pton($ip);
        $binaryProxy = inet_pton($proxyIP);

        if ($binaryIP === false || $binaryProxy === false) {
            return false;
        }

        // If the proxy entry doesn't match the IP protocol - no match
        if (strlen($binaryIP) !== strlen($binaryProxy)) {
            return false;
        }

        if ($maskLength === null) {
            return $binaryIP === $binaryProxy;
        }

        if ($maskLength > strlen($binaryIP) * 8) {
            return false;
        }

        if ($maskLength === 0) {
            return true;
        }

        $fullBytes     = intdiv($maskLength, 8);
        $remainingBits = $maskLength % 8;

        if ($fullBytes > 0 && strncmp($binaryIP, $binaryProxy, $fullBytes) !== 0) {
            return false;
        }

        if ($remainingBits > 0) {
            $bitmask = 0xFF & (0xFF << (8 - $remainingBits));

            return (ord($binaryIP[$fullBytes]) & $bitmask) === (ord($binaryProxy[$fullBytes]) & $bitmask);
        }

        return true;
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
        // Keep BC with $globals array
        $this->globals[$name] = $value;

        // Also update Superglobals via service
        service('superglobals')->setGlobalArray($name, $value);

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
     * @return mixed
     */
    public function fetchGlobal(string $name, $index = null, ?int $filter = null, $flags = null)
    {
        if (! isset($this->globals[$name])) {
            $this->populateGlobals($name);
        }

        // Null filters cause null values to return.
        $filter ??= FILTER_UNSAFE_RAW;
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
                $filter !== FILTER_UNSAFE_RAW
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
     *
     * @deprecated 4.7.0 No longer needs to be called explicitly. Used internally to maintain BC with $globals.
     */
    protected function populateGlobals(string $name)
    {
        if (! isset($this->globals[$name])) {
            $this->globals[$name] = [];
        }

        // Get data from Superglobals service instead of direct access
        $this->globals[$name] = service('superglobals')->getGlobalArray($name);
    }
}
