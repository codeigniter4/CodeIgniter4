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
use Config\Services;

/**
 * Debug toolbar filter
 *
 * @see \CodeIgniter\Filters\DebugToolbarTest
 */
class DebugToolbar implements FilterInterface
{
    /**
     * We don't need to do anything here.
     *
     * @param array|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null)
    {
    }

    /**
     * If the debug flag is set (CI_DEBUG) then collect performance
     * and debug information and display it in a toolbar.
     *
     * @param array|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        Services::toolbar()->prepare($request, $response);
    }
}
