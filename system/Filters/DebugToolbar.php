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

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Debug toolbar filter
 */
class DebugToolbar implements FilterInterface
{
    /**
     * We don't need to do anything here.
     *
     * @param IncomingRequest|RequestInterface $request
     * @param array|null                       $arguments
     *
     * @return void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
    }

    //--------------------------------------------------------------------

    /**
     * If the debug flag is set (CI_DEBUG) then collect performance
     * and debug information and display it in a toolbar.
     *
     * @param IncomingRequest|RequestInterface $request
     * @param Response|ResponseInterface       $response
     * @param array|null                       $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        Services::toolbar()->prepare($request, $response);
    }

    //--------------------------------------------------------------------
}
