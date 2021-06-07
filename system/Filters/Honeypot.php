<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Filters;

use CodeIgniter\Honeypot\Exceptions\HoneypotException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Honeypot filter
 */
class Honeypot implements FilterInterface
{
    /**
     * Checks if Honeypot field is empty; if not
     * then the requester is a bot
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $honeypot = Services::honeypot(new \Config\Honeypot());
        if ($honeypot->hasContent($request)) {
            throw HoneypotException::isBot();
        }
    }

    /**
     * Attach a honeypot to the current response.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $honeypot = Services::honeypot(new \Config\Honeypot());
        $honeypot->attachHoneypot($response);
    }
}
