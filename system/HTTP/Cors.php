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
use Config\Cors as CorsConfig;

/**
 * Cross-Origin Resource Sharing (CORS)
 *
 * @see \CodeIgniter\HTTP\CorsTest
 */
class Cors
{
    /**
     * @var array{
     *     allowedOrigins: list<string>,
     *     allowedOriginsPatterns: list<string>,
     *     supportsCredentials: bool,
     *     allowedHeaders: list<string>,
     *     exposedHeaders: list<string>,
     *     allowedMethods: list<string>,
     *     maxAge: int,
     * }
     */
    private array $config = [
        'allowedOrigins'         => [],
        'allowedOriginsPatterns' => [],
        'supportsCredentials'    => false,
        'allowedHeaders'         => [],
        'exposedHeaders'         => [],
        'allowedMethods'         => [],
        'maxAge'                 => 7200,
    ];

    /**
     * @param array{
     *     allowedOrigins?: list<string>,
     *     allowedOriginsPatterns?: list<string>,
     *     supportsCredentials?: bool,
     *     allowedHeaders?: list<string>,
     *     exposedHeaders?: list<string>,
     *     allowedMethods?: list<string>,
     *     maxAge?: int,
     * }|CorsConfig|null $config
     */
    public function __construct($config = null)
    {
        $config ??= config(CorsConfig::class);
        if ($config instanceof CorsConfig) {
            $config = (array) $config;
        }
        $this->config = array_merge($this->config, $config);
    }

    public function isPreflightRequest(IncomingRequest $request): bool
    {
        return $request->is('OPTIONS')
            && $request->hasHeader('Access-Control-Request-Method');
    }

    public function handlePreflightRequest(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $response->setStatusCode(204);

        $this->setAllowOrigin($request, $response);

        if ($response->hasHeader('Access-Control-Allow-Origin')) {
            $this->setAllowHeaders($response);
            $this->setAllowMethods($response);
            $this->setAllowMaxAge($response);
            $this->setAllowCredentials($response);
        }

        return $response;
    }

    private function setAllowOrigin(RequestInterface $request, ResponseInterface $response): void
    {
        $originCount        = count($this->config['allowedOrigins']);
        $originPatternCount = count($this->config['allowedOriginsPatterns']);

        if (in_array('*', $this->config['allowedOrigins'], true) && $originCount > 1) {
            throw new ConfigException(
                "If wildcard is specified, you must set `'allowedOrigins' => ['*']`."
            );
        }

        if (
            $originCount === 1 && $this->config['allowedOrigins'][0] === '*'
            && $this->config['supportsCredentials']
        ) {
            throw new ConfigException(
                'When responding to a credentialed request, the server must not specify the "*" wildcard for the Access-Control-Allow-Origin response-header value.'
            );
        }

        if ($originCount === 1 && $originPatternCount === 0) {
            $response->setHeader('Access-Control-Allow-Origin', $this->config['allowedOrigins'][0]);

            return;
        }

        $origin = $request->getHeaderLine('Origin');

        if ($originCount > 1 && in_array($origin, $this->config['allowedOrigins'], true)) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
            $response->appendHeader('Vary', 'Origin');

            return;
        }

        if ($originPatternCount > 0) {
            foreach ($this->config['allowedOriginsPatterns'] as $pattern) {
                $regex = '#\A' . $pattern . '\z#';

                if (preg_match($regex, $origin)) {
                    $response->setHeader('Access-Control-Allow-Origin', $origin);
                    $response->appendHeader('Vary', 'Origin');
                }
            }
        }
    }

    private function setAllowHeaders(ResponseInterface $response): void
    {
        if (
            in_array('*', $this->config['allowedHeaders'], true)
            && count($this->config['allowedHeaders']) > 1
        ) {
            throw new ConfigException(
                "If wildcard is specified, you must set `'allowedHeaders' => ['*']`."
            );
        }

        if (
            $this->config['allowedHeaders'][0] === '*'
            && $this->config['supportsCredentials']
        ) {
            throw new ConfigException(
                'When responding to a credentialed request, the server must not specify the "*" wildcard for the Access-Control-Allow-Headers response-header value.'
            );
        }

        $response->setHeader(
            'Access-Control-Allow-Headers',
            implode(', ', $this->config['allowedHeaders'])
        );
    }

    private function setAllowMethods(ResponseInterface $response): void
    {
        if (
            in_array('*', $this->config['allowedMethods'], true)
            && count($this->config['allowedMethods']) > 1
        ) {
            throw new ConfigException(
                "If wildcard is specified, you must set `'allowedMethods' => ['*']`."
            );
        }

        if (
            $this->config['allowedMethods'][0] === '*'
            && $this->config['supportsCredentials']
        ) {
            throw new ConfigException(
                'When responding to a credentialed request, the server must not specify the "*" wildcard for the Access-Control-Allow-Methods response-header value.'
            );
        }

        $response->setHeader(
            'Access-Control-Allow-Methods',
            implode(', ', $this->config['allowedMethods'])
        );
    }

    private function setAllowMaxAge(ResponseInterface $response): void
    {
        $response->setHeader('Access-Control-Max-Age', (string) $this->config['maxAge']);
    }

    private function setAllowCredentials(ResponseInterface $response): void
    {
        if ($this->config['supportsCredentials']) {
            $response->setHeader('Access-Control-Allow-Credentials', 'true');
        }
    }

    public function addResponseHeaders(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->setAllowOrigin($request, $response);

        if ($response->hasHeader('Access-Control-Allow-Origin')) {
            $this->setAllowCredentials($response);
            $this->setExposeHeaders($response);
        }

        return $response;
    }

    private function setExposeHeaders(ResponseInterface $response): void
    {
        if ($this->config['exposedHeaders'] !== []) {
            $response->setHeader(
                'Access-Control-Expose-Headers',
                implode(', ', $this->config['exposedHeaders'])
            );
        }
    }
}
