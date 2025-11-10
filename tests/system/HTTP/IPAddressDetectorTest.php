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
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class IPAddressDetectorTest extends CIUnitTestCase
{
    private IPAddressDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new IPAddressDetector();
    }

    public function testDetectWithNoProxies(): void
    {
        $remoteAddr = '192.168.1.100';
        $proxyIPs   = [];
        $headerGetter = static fn(string $name): ?string => null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        $this->assertSame('192.168.1.100', $result);
    }

    public function testDetectWithInvalidIP(): void
    {
        $remoteAddr = 'invalid-ip';
        $proxyIPs   = [];
        $headerGetter = static fn(string $name): ?string => null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        $this->assertSame('0.0.0.0', $result);
    }

    public function testDetectWithTrustedProxyExactMatch(): void
    {
        $remoteAddr = '192.168.1.1';
        $proxyIPs   = ['192.168.1.1' => 'X-Forwarded-For'];
        $headerGetter = static fn(string $name): ?string => $name === 'X-Forwarded-For' ? '203.0.113.5' : null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        $this->assertSame('203.0.113.5', $result);
    }

    public function testDetectWithUntrustedProxy(): void
    {
        $remoteAddr = '192.168.1.100';
        $proxyIPs   = ['192.168.1.1' => 'X-Forwarded-For'];
        $headerGetter = static fn(string $name): ?string => $name === 'X-Forwarded-For' ? '203.0.113.5' : null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        // Should return the original IP since it's not from a trusted proxy
        $this->assertSame('192.168.1.100', $result);
    }

    public function testDetectWithTrustedProxySubnet(): void
    {
        $remoteAddr = '192.168.1.50';
        $proxyIPs   = ['192.168.1.0/24' => 'X-Forwarded-For'];
        $headerGetter = static fn(string $name): ?string => $name === 'X-Forwarded-For' ? '203.0.113.5' : null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        $this->assertSame('203.0.113.5', $result);
    }

    public function testDetectWithIPOutsideSubnet(): void
    {
        $remoteAddr = '192.168.2.50';
        $proxyIPs   = ['192.168.1.0/24' => 'X-Forwarded-For'];
        $headerGetter = static fn(string $name): ?string => $name === 'X-Forwarded-For' ? '203.0.113.5' : null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        // Should return the original IP since it's outside the trusted subnet
        $this->assertSame('192.168.2.50', $result);
    }

    public function testDetectWithMultipleIPsInHeader(): void
    {
        $remoteAddr = '192.168.1.1';
        $proxyIPs   = ['192.168.1.1' => 'X-Forwarded-For'];
        $headerGetter = static fn(string $name): ?string => $name === 'X-Forwarded-For' ? '203.0.113.5, 198.51.100.1, 192.0.2.1' : null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        // Should extract only the first IP from the comma-separated list
        $this->assertSame('203.0.113.5', $result);
    }

    public function testDetectWithInvalidClientIP(): void
    {
        $remoteAddr = '192.168.1.1';
        $proxyIPs   = ['192.168.1.1' => 'X-Forwarded-For'];
        $headerGetter = static fn(string $name): ?string => $name === 'X-Forwarded-For' ? 'invalid-ip' : null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        // Should return the original IP since the client IP in the header is invalid
        $this->assertSame('192.168.1.1', $result);
    }

    public function testDetectWithMissingHeader(): void
    {
        $remoteAddr = '192.168.1.1';
        $proxyIPs   = ['192.168.1.1' => 'X-Forwarded-For'];
        $headerGetter = static fn(string $name): ?string => null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        // Should return the original IP since the header is not present
        $this->assertSame('192.168.1.1', $result);
    }

    public function testDetectWithIPv6(): void
    {
        $remoteAddr = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
        $proxyIPs   = [];
        $headerGetter = static fn(string $name): ?string => null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        $this->assertSame('2001:0db8:85a3:0000:0000:8a2e:0370:7334', $result);
    }

    public function testDetectWithIPv6Subnet(): void
    {
        $remoteAddr = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
        $proxyIPs   = ['2001:0db8:85a3::/64' => 'X-Forwarded-For'];
        $headerGetter = static fn(string $name): ?string => $name === 'X-Forwarded-For' ? '2001:0db8:1234::1' : null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        $this->assertSame('2001:0db8:1234::1', $result);
    }

    public function testDetectWithIPv6OutsideSubnet(): void
    {
        $remoteAddr = '2001:0db8:1234:0000:0000:0000:0000:0001';
        $proxyIPs   = ['2001:0db8:85a3::/64' => 'X-Forwarded-For'];
        $headerGetter = static fn(string $name): ?string => $name === 'X-Forwarded-For' ? '2001:0db8:5678::1' : null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        // Should return the original IP since it's outside the trusted subnet
        $this->assertSame('2001:0db8:1234:0000:0000:0000:0000:0001', $result);
    }

    public function testDetectWithMixedIPv4AndIPv6Proxies(): void
    {
        $remoteAddr = '192.168.1.50';
        $proxyIPs   = [
            '2001:0db8:85a3::/64' => 'X-Real-IP',
            '192.168.1.0/24'      => 'X-Forwarded-For',
        ];
        $headerGetter = static fn(string $name): ?string => $name === 'X-Forwarded-For' ? '203.0.113.5' : null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        // Should match the IPv4 subnet and extract the client IP
        $this->assertSame('203.0.113.5', $result);
    }

    public function testDetectWithInvalidProxyIPsConfiguration(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('You must set an array with Proxy IP address key and HTTP header name value in Config\App::$proxyIPs.');

        $remoteAddr = '192.168.1.100';
        $proxyIPs   = ['192.168.1.1']; // Invalid: indexed array instead of associative
        $headerGetter = static fn(string $name): ?string => null;

        $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);
    }

    public function testDetectWithMultipleTrustedProxies(): void
    {
        $remoteAddr = '192.168.1.1';
        $proxyIPs   = [
            '192.168.1.1' => 'X-Forwarded-For',
            '10.0.0.1'    => 'X-Real-IP',
        ];
        $headerGetter = static fn(string $name): ?string => $name === 'X-Forwarded-For' ? '203.0.113.5' : null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        // Should match the first trusted proxy and extract the client IP
        $this->assertSame('203.0.113.5', $result);
    }

    #[DataProvider('provideSubnetTestCases')]
    public function testSubnetMatching(string $remoteAddr, string $subnet, bool $shouldMatch): void
    {
        $proxyIPs = [$subnet => 'X-Forwarded-For'];
        $headerGetter = static fn(string $name): ?string => $name === 'X-Forwarded-For' ? '203.0.113.5' : null;

        $result = $this->detector->detect($remoteAddr, $proxyIPs, $headerGetter);

        if ($shouldMatch) {
            $this->assertSame('203.0.113.5', $result, "Expected IP from header for {$remoteAddr} in {$subnet}");
        } else {
            $this->assertSame($remoteAddr, $result, "Expected original IP for {$remoteAddr} not in {$subnet}");
        }
    }

    public static function provideSubnetTestCases(): iterable
    {
        return [
            'IPv4 /24 match'      => ['192.168.1.50', '192.168.1.0/24', true],
            'IPv4 /24 no match'   => ['192.168.2.50', '192.168.1.0/24', false],
            'IPv4 /16 match'      => ['192.168.50.1', '192.168.0.0/16', true],
            'IPv4 /16 no match'   => ['192.169.1.1', '192.168.0.0/16', false],
            'IPv4 /8 match'       => ['10.50.100.200', '10.0.0.0/8', true],
            'IPv4 /8 no match'    => ['11.0.0.1', '10.0.0.0/8', false],
            'IPv4 /32 exact'      => ['192.168.1.1', '192.168.1.1/32', true],
            'IPv4 /32 no match'   => ['192.168.1.2', '192.168.1.1/32', false],
        ];
    }
}
