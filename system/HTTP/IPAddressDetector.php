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

/**
 * IP Address Detector
 *
 * Handles detection of client IP addresses, including support for
 * proxy servers and subnet matching for trusted proxies.
 */
class IPAddressDetector
{
    /**
     * IP validator callable.
     *
     * @var callable
     */
    private $ipValidator;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ipValidator = [
            new FormatRules(),
            'valid_ip',
        ];
    }

    /**
     * Detects the client's IP address.
     *
     * @param string                $remoteAddr The REMOTE_ADDR value from the server
     * @param array<string, string> $proxyIPs   Array of proxy IP addresses with their corresponding headers
     * @param callable              $headerGetter Callback to get header values by name
     *
     * @return string The detected IP address, or '0.0.0.0' if invalid
     *
     * @throws ConfigException
     */
    public function detect(string $remoteAddr, array $proxyIPs, callable $headerGetter): string
    {
        // Validate proxy IPs configuration
        if (! empty($proxyIPs) && (! is_array($proxyIPs) || is_int(array_key_first($proxyIPs)))) {
            throw new ConfigException(
                'You must set an array with Proxy IP address key and HTTP header name value in Config\App::$proxyIPs.',
            );
        }

        $ipAddress = $remoteAddr;

        // Check if the request is coming from a trusted proxy
        foreach ($proxyIPs as $proxyIP => $header) {
            if ($this->isFromTrustedProxy($ipAddress, $proxyIP)) {
                $clientIP = $this->extractClientIP($header, $headerGetter);

                if ($clientIP !== null) {
                    $ipAddress = $clientIP;
                    break;
                }
            }
        }

        // Validate the final IP address
        if (! ($this->ipValidator)($ipAddress)) {
            return '0.0.0.0';
        }

        return $ipAddress;
    }

    /**
     * Checks if the current IP address is from a trusted proxy.
     *
     * @param string $ipAddress The IP address to check
     * @param string $proxyIP   The trusted proxy IP or subnet (e.g., '192.168.1.1' or '192.168.1.0/24')
     */
    private function isFromTrustedProxy(string $ipAddress, string $proxyIP): bool
    {
        // Check if we have an IP address or a subnet
        if (! str_contains($proxyIP, '/')) {
            // An IP address (and not a subnet) is specified.
            // We can compare right away.
            return $proxyIP === $ipAddress;
        }

        // We have a subnet ... now the heavy lifting begins
        return $this->isIPInSubnet($ipAddress, $proxyIP);
    }

    /**
     * Checks if an IP address is within a given subnet.
     *
     * @param string $ipAddress The IP address to check
     * @param string $subnet    The subnet in CIDR notation (e.g., '192.168.1.0/24')
     */
    private function isIPInSubnet(string $ipAddress, string $subnet): bool
    {
        // Determine if we're dealing with IPv4 or IPv6
        $separator = ($this->ipValidator)($ipAddress, 'ipv6') ? ':' : '.';

        // If the proxy entry doesn't match the IP protocol - skip it
        if (! str_contains($subnet, $separator)) {
            return false;
        }

        // Convert the IP address to binary
        $ipBinary = $this->convertIPToBinary($ipAddress, $separator);

        // Split the netmask length off the network address
        sscanf($subnet, '%[^/]/%d', $netaddr, $masklen);

        // Convert the network address to binary
        $netaddrBinary = $this->convertIPToBinary($netaddr, $separator);

        // Compare the binary representations
        return strncmp($ipBinary, $netaddrBinary, $masklen) === 0;
    }

    /**
     * Converts an IP address to its binary representation.
     *
     * @param string $ipAddress The IP address to convert
     * @param string $separator The separator character (':' for IPv6, '.' for IPv4)
     */
    private function convertIPToBinary(string $ipAddress, string $separator): string
    {
        if ($separator === ':') {
            // IPv6 address
            // Make sure we're having the "full" IPv6 format
            $ip = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($ipAddress, ':')), $ipAddress));

            for ($j = 0; $j < 8; $j++) {
                $ip[$j] = intval($ip[$j], 16);
            }

            $sprintf = '%016b%016b%016b%016b%016b%016b%016b%016b';
        } else {
            // IPv4 address
            $ip      = explode('.', $ipAddress);
            $sprintf = '%08b%08b%08b%08b';
        }

        return vsprintf($sprintf, $ip);
    }

    /**
     * Extracts the client IP address from an HTTP header.
     *
     * @param string   $headerName   The name of the header to check
     * @param callable $headerGetter Callback to get header values by name
     *
     * @return string|null The client IP address, or null if not found or invalid
     */
    private function extractClientIP(string $headerName, callable $headerGetter): ?string
    {
        $headerValue = $headerGetter($headerName);

        if ($headerValue === null) {
            return null;
        }

        // Some proxies typically list the whole chain of IP
        // addresses through which the client has reached us.
        // e.g. client_ip, proxy_ip1, proxy_ip2, etc.
        sscanf($headerValue, '%[^,]', $clientIP);

        if (! ($this->ipValidator)($clientIP)) {
            return null;
        }

        return $clientIP;
    }
}
