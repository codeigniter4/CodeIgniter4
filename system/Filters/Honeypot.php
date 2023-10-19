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

use CodeIgniter\Honeypot\Exceptions\HoneypotException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Honeypot filter
 *
 * @see \CodeIgniter\Filters\HoneypotTest
 */
class Honeypot implements FilterInterface
{
    /**
     * Checks if Honeypot field is empty, if not then the
     * requester is a bot
     *
     * @param array|null $arguments
     *
     * @throws HoneypotException
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return;
        }

        if (Services::honeypot()->hasContent($request)) {
            throw HoneypotException::isBot();
        }
    }

    /**
     * Attach a honeypot to the current response.
     *
     * @param array|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        Services::honeypot()->attachHoneypot($response);
    }
}
