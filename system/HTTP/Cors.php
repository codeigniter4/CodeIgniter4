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
            $config = $config->default;
        }
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Creates a new instance by config name.
     */
    public static function factory(string $configName = 'default'): self
    {
        $config = config(CorsConfig::class)->{$configName};

        return new self($config);
    }

    /**
     * Whether if the request is a preflight request.
     */
    public function isPreflightRequest(IncomingRequest $request): bool
    {
        return $request->is('OPTIONS')
            && $request->hasHeader('Access-Control-Request-Method');
    }

    /**
     * Handles the preflight request, and returns the response.
     */
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

    private function checkWildcard(string $name, int $count): void
    {
        if (in_array('*', $this->config[$name], true) && $count > 1) {
            throw new ConfigException(
                "If wildcard is specified, you must set `'{$name}' => ['*']`."
                . ' But using wildcard is not recommended.'
            );
        }
    }

    private function checkWildcardAndCredentials(string $name, string $header): void
    {
        if (
            $this->config[$name] === ['*']
            && $this->config['supportsCredentials']
        ) {
            throw new ConfigException(
                'When responding to a credentialed request, '
                . 'the server must not specify the "*" wildcard for the '
                . $header . ' response-header value.'
            );
        }
    }

    private function setAllowOrigin(RequestInterface $request, ResponseInterface $response): void
    {
        $originCount        = count($this->config['allowedOrigins']);
        $originPatternCount = count($this->config['allowedOriginsPatterns']);

        $this->checkWildcard('allowedOrigins', $originCount);
        $this->checkWildcardAndCredentials('allowedOrigins', 'Access-Control-Allow-Origin');

        // Single Origin.
        if ($originCount === 1 && $originPatternCount === 0) {
            $response->setHeader('Access-Control-Allow-Origin', $this->config['allowedOrigins'][0]);

            return;
        }

        // Multiple Origins.
        if (! $request->hasHeader('Origin')) {
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

                    return;
                }
            }
        }
    }

    private function setAllowHeaders(ResponseInterface $response): void
    {
        $this->checkWildcard('allowedHeaders', count($this->config['allowedHeaders']));
        $this->checkWildcardAndCredentials('allowedHeaders', 'Access-Control-Allow-Headers');

        $response->setHeader(
            'Access-Control-Allow-Headers',
            implode(', ', $this->config['allowedHeaders'])
        );
    }

    private function setAllowMethods(ResponseInterface $response): void
    {
        $this->checkWildcard('allowedMethods', count($this->config['allowedMethods']));
        $this->checkWildcardAndCredentials('allowedMethods', 'Access-Control-Allow-Methods');

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

    /**
     * Adds CORS headers to the Response.
     */
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
