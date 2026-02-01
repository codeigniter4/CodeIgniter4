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
        $allowedHosts = array_map(strtolower(...), (array) $this->hostname);

        if (! in_array($currentHost, $allowedHosts, true)) {
            throw new PageNotFoundException('Access denied: Host is not allowed.');
        }
    }

    private function checkSubdomain(RequestInterface $request): void
    {
        if ($this->subdomain === null || $this->subdomain === []) {
            return;
        }

        $currentSubdomain  = parse_subdomain($request->getUri()->getHost());
        $allowedSubdomains = array_map(strtolower(...), (array) $this->subdomain);

        // If no subdomain exists but one is required
        if ($currentSubdomain === '') {
            throw new PageNotFoundException('Access denied: Subdomain required');
        }

        // Check if the current subdomain is allowed
        if (! in_array($currentSubdomain, $allowedSubdomains, true)) {
            throw new PageNotFoundException('Access denied: subdomain is blocked.');
        }
    }
}
