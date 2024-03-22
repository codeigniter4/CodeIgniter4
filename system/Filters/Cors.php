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
    private CorsService $cors;

    /**
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
        $this->cors = new CorsService($config);
    }

    /**
     * @param array<string, list<string>>|null $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return;
        }

        /** @var ResponseInterface $response */
        $response = service('response');

        if ($this->cors->isPreflightRequest($request)) {
            return $this->cors->handlePreflightRequest($request, $response);
        }
    }

    /**
     * @param array<string, list<string>>|null $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return;
        }

        return $this->cors->addResponseHeaders($request, $response);
    }
}
