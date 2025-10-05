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

namespace CodeIgniter\Router\Attributes;

use Attribute;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Restrict Attribute
 *
 * Restricts access to controller methods or entire controllers based on environment,
 * hostname, or subdomain conditions. Throws PageNotFoundException when restrictions
 * are not met.
 *
 * Limitations:
 * - Throws PageNotFoundException (404) for all restriction failures
 * - Cannot provide custom error messages or HTTP status codes
 * - Subdomain detection may not work correctly behind proxies without proper configuration
 * - Does not support wildcard or regex patterns for hostnames
 * - Cannot restrict based on request headers, IP addresses, or user authentication
 *
 * Security Considerations:
 * - Environment checks rely on the ENVIRONMENT constant being correctly set
 * - Hostname restrictions can be bypassed if Host header is not validated at web server level
 * - Should not be used as the sole security mechanism for sensitive operations
 * - Consider additional authorization checks for critical endpoints
 * - Does not prevent direct access if routes are exposed through other means
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Restrict implements RouteAttributeInterface
{
    private const TWO_PART_TLDS = [
        'co.uk', 'org.uk', 'gov.uk', 'ac.uk', 'sch.uk', 'ltd.uk', 'plc.uk',
        'com.au', 'net.au', 'org.au', 'edu.au', 'gov.au', 'asn.au', 'id.au',
        'co.jp', 'ac.jp', 'go.jp', 'or.jp', 'ne.jp', 'gr.jp',
        'co.nz', 'org.nz', 'govt.nz', 'ac.nz', 'net.nz', 'geek.nz', 'maori.nz', 'school.nz',
        'co.in', 'net.in', 'org.in', 'ind.in', 'ac.in', 'gov.in', 'res.in',
        'com.cn', 'net.cn', 'org.cn', 'gov.cn', 'edu.cn',
        'com.sg', 'net.sg', 'org.sg', 'gov.sg', 'edu.sg', 'per.sg',
        'co.za', 'org.za', 'gov.za', 'ac.za', 'net.za',
        'co.kr', 'or.kr', 'go.kr', 'ac.kr', 'ne.kr', 'pe.kr',
        'co.th', 'or.th', 'go.th', 'ac.th', 'net.th', 'in.th',
        'com.my', 'net.my', 'org.my', 'edu.my', 'gov.my', 'mil.my', 'name.my',
        'com.mx', 'org.mx', 'net.mx', 'edu.mx', 'gob.mx',
        'com.br', 'net.br', 'org.br', 'gov.br', 'edu.br', 'art.br', 'eng.br',
        'co.il', 'org.il', 'ac.il', 'gov.il', 'net.il', 'muni.il',
        'co.id', 'or.id', 'ac.id', 'go.id', 'net.id', 'web.id', 'my.id',
        'com.hk', 'edu.hk', 'gov.hk', 'idv.hk', 'net.hk', 'org.hk',
        'com.tw', 'net.tw', 'org.tw', 'edu.tw', 'gov.tw', 'idv.tw',
        'com.sa', 'net.sa', 'org.sa', 'gov.sa', 'edu.sa', 'sch.sa', 'med.sa',
        'co.ae', 'net.ae', 'org.ae', 'gov.ae', 'ac.ae', 'sch.ae',
        'com.tr', 'net.tr', 'org.tr', 'gov.tr', 'edu.tr', 'av.tr', 'gen.tr',
        'co.ke', 'or.ke', 'go.ke', 'ac.ke', 'sc.ke', 'me.ke', 'mobi.ke', 'info.ke',
        'com.ng', 'org.ng', 'gov.ng', 'edu.ng', 'net.ng', 'sch.ng', 'name.ng',
        'com.pk', 'net.pk', 'org.pk', 'gov.pk', 'edu.pk', 'fam.pk',
        'com.eg', 'edu.eg', 'gov.eg', 'org.eg', 'net.eg',
        'com.cy', 'net.cy', 'org.cy', 'gov.cy', 'ac.cy',
        'com.lk', 'org.lk', 'edu.lk', 'gov.lk', 'net.lk', 'int.lk',
        'com.bd', 'net.bd', 'org.bd', 'ac.bd', 'gov.bd', 'mil.bd',
        'com.ar', 'net.ar', 'org.ar', 'gov.ar', 'edu.ar', 'mil.ar',
        'gob.cl',
    ];

    public function __construct(
        public array|string|null $environment = null,
        public array|string|null $hostname = null,
        public array|string|null $subdomain = null,
    ) {
    }

    public function before(RequestInterface $request): RequestInterface|ResponseInterface|null
    {
        $this->checkEnvironment();
        $this->checkHostname($request);
        $this->checkSubdomain($request);

        return null; // Continue normal execution
    }

    public function after(RequestInterface $request, ResponseInterface $response): ?ResponseInterface
    {
        return null; // No post-processing needed
    }

    protected function checkEnvironment(): void
    {
        if ($this->environment === null || $this->environment === []) {
            return;
        }

        $currentEnv = ENVIRONMENT;
        $allowed    = [];
        $denied     = [];

        foreach ((array) $this->environment as $env) {
            if (str_starts_with($env, '!')) {
                $denied[] = substr($env, 1);
            } else {
                $allowed[] = $env;
            }
        }

        // Check denied environments first (explicit deny takes precedence)
        if ($denied !== [] && in_array($currentEnv, $denied, true)) {
            throw new PageNotFoundException('Access denied: Current environment is blocked.');
        }

        // If allowed list exists, current env must be in it
        // If no allowed list (only denials), then all non-denied envs are allowed
        if ($allowed !== [] && ! in_array($currentEnv, $allowed, true)) {
            throw new PageNotFoundException('Access denied: Current environment is not allowed.');
        }
    }

    private function checkHostname(RequestInterface $request): void
    {
        if ($this->hostname === null || $this->hostname === []) {
            return;
        }

        $currentHost  = strtolower($request->getUri()->getHost());
        $allowedHosts = array_map('strtolower', (array) $this->hostname);

        if (! in_array($currentHost, $allowedHosts, true)) {
            throw new PageNotFoundException('Access denied: Host is not allowed.');
        }
    }

    private function checkSubdomain(RequestInterface $request): void
    {
        if ($this->subdomain === null || $this->subdomain === []) {
            return;
        }

        $currentSubdomain  = $this->getSubdomain($request);
        $allowedSubdomains = array_map('strtolower', (array) $this->subdomain);

        // If no subdomain exists but one is required
        if ($currentSubdomain === '') {
            throw new PageNotFoundException('Access denied: Subdomain required');
        }

        // Check if the current subdomain is allowed
        if (! in_array($currentSubdomain, $allowedSubdomains, true)) {
            throw new PageNotFoundException('Access denied: subdomain is blocked.');
        }
    }

    private function getSubdomain(RequestInterface $request): string
    {
        $host = strtolower($request->getUri()->getHost());

        // Handle localhost and IP addresses - they don't have subdomains
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return '';
        }

        $parts     = explode('.', $host);
        $partCount = count($parts);

        // Need at least 3 parts for a subdomain (subdomain.domain.tld)
        // e.g., api.example.com
        if ($partCount < 3) {
            return '';
        }

        // Check if we have a two-part TLD (e.g., co.uk, com.au)
        if ($partCount >= 3) {
            $lastTwoParts = $parts[$partCount - 2] . '.' . $parts[$partCount - 1];

            if (in_array($lastTwoParts, self::TWO_PART_TLDS, true)) {
                // For two-part TLD, need at least 4 parts for subdomain
                // e.g., api.example.co.uk (4 parts)
                if ($partCount < 4) {
                    return ''; // No subdomain, just domain.co.uk
                }

                // Remove the two-part TLD and domain name (last 3 parts)
                // e.g., admin.api.example.co.uk -> admin.api
                return implode('.', array_slice($parts, 0, $partCount - 3));
            }
        }

        // Standard TLD: Remove TLD and domain (last 2 parts)
        // e.g., admin.api.example.com -> admin.api
        return implode('.', array_slice($parts, 0, $partCount - 2));
    }
}
