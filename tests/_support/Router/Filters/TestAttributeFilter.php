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

namespace Tests\Support\Router\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TestAttributeFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if ($arguments !== null) {
            $arguments = '(' . implode(',', $arguments) . ')';
        }
        // Modify request body to indicate filter ran
        $request->setBody('before_filter_ran' . $arguments . ':');

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if ($arguments !== null) {
            $arguments = '(' . implode(',', $arguments) . ')';
        }
        // Append to response body to indicate filter ran
        $body = $response->getBody();
        $response->setBody($body . ':after_filter_ran' . $arguments);

        return $response;
    }
}
