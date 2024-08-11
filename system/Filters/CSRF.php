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

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Security\Security;

/**
 * CSRF filter.
 *
 * This filter is not intended to be used from the command line.
 *
 * @codeCoverageIgnore
 * @see \CodeIgniter\Filters\CSRFTest
 */
class CSRF implements FilterInterface
{
    /**
     * CSRF verification.
     *
     * @param list<string>|null $arguments
     *
     * @return RedirectResponse|void
     *
     * @throws SecurityException
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return;
        }

        /** @var Security $security */
        $security = service('security');

        try {
            $security->verify($request);
        } catch (SecurityException $e) {
            if ($security->shouldRedirect() && ! $request->isAJAX()) {
                return redirect()->back()->with('error', $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * We don't have anything to do here.
     *
     * @param list<string>|null $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
