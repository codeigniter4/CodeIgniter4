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

namespace CodeIgniter\Filters;

use CodeIgniter\HTTP\Cors as CorsService;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * @see \CodeIgniter\Filters\CorsTest
 */
class Cors implements FilterInterface
{
    private ?CorsService $cors = null;

    /**
     * @testTag $config is used for testing purposes only.
     *
     * @param array{
     *      allowedOrigins?: list<string>,
     *      allowedOriginsPatterns?: list<string>,
     *      supportsCredentials?: bool,
     *      allowedHeaders?: list<string>,
     *      exposedHeaders?: list<string>,
     *      allowedMethods?: list<string>,
     *      maxAge?: int,
     *  } $config
     */
    public function __construct(array $config = [])
    {
        if ($config !== []) {
            $this->cors = new CorsService($config);
        }
    }

    /**
     * @param list<string>|null $arguments
     *
     * @return ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return;
        }

        $this->createCorsService($arguments);

        if (! $this->cors->isPreflightRequest($request)) {
            return;
        }

        /** @var ResponseInterface $response */
        $response = service('response');

        $response = $this->cors->handlePreflightRequest($request, $response);

        // Always adds `Vary: Origin` header because if there is no Origin header
        // in a OPTIONS request, the request may be cached on an intermediate
        // cache server such as a CDN.
        $response->appendHeader('Vary', 'Origin');

        return $response;
    }

    /**
     * @param list<string>|null $arguments
     */
    private function createCorsService(?array $arguments): void
    {
        $this->cors ??= ($arguments === null) ? CorsService::factory()
            : CorsService::factory($arguments[0]);
    }

    /**
     * @param list<string>|null $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return;
        }

        $this->createCorsService($arguments);

        // Always adds `Vary: Origin` header because if there is no Origin header
        // in a OPTIONS request, the request may be cached on an intermediate
        // cache server such as a CDN.
        if ($request->is('OPTIONS')) {
            $response->appendHeader('Vary', 'Origin');
        }

        return $this->cors->addResponseHeaders($request, $response);
    }
}
