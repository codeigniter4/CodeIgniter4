<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RequestId implements FilterInterface
{
    /**
     * Generates a unique request ID for each incoming request and adds it to the request context.
     * If the incoming request already has X-Request-ID header, then that header is used instead.
     *
     * {@inheritDoc}
     */
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $requestId = trim($request->getHeaderLine('X-Request-ID'));

        if (! $this->isValidRequestId($requestId)) {
            $requestId = bin2hex(random_bytes(16));
        }

        context()->set('request_id', $requestId);

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ?ResponseInterface
    {
        $response->setHeader('X-Request-ID', context()->get('request_id'));

        return null;
    }

    private function isValidRequestId(string $requestId): bool
    {
        if ($requestId === '') {
            return false;
        }

        if (strlen($requestId) > 255) {
            return false;
        }

        return preg_match('/^[A-Za-z0-9._:-]+$/', $requestId) === 1;
    }
}
