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
use CodeIgniter\Security\Exceptions\SecurityException;
use Config\Services;

/**
 * CSRF filter.
 *
 * This filter is not intended to be used from the command line.
 *
 * @codeCoverageIgnore
 */
class CSRF implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param IncomingRequest|RequestInterface $request
     * @param array|null                       $arguments
     *
     * @throws SecurityException
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if ($request->isCLI()) {
            return;
        }

        $security = Services::security();

        try {
            $security->verify($request);
        } catch (SecurityException $e) {
            if ($security->shouldRedirect() && ! $request->isAJAX()) {
                return redirect()->back()->with('error', $e->getMessage());
            }

            throw $e;
        }
    }

    //--------------------------------------------------------------------

    /**
     * We don't have anything to do here.
     *
     * @param IncomingRequest|RequestInterface $request
     * @param Response|ResponseInterface       $response
     * @param array|null                       $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    //--------------------------------------------------------------------
}
