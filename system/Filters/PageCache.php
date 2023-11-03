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

use CodeIgniter\Cache\ResponseCache;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Page Cache filter
 */
class PageCache implements FilterInterface
{
    private ResponseCache $pageCache;

    public function __construct()
    {
        $this->pageCache = Services::responsecache();
    }

    /**
     * Checks page cache and return if found.
     *
     * @param array|null $arguments
     *
     * @return ResponseInterface|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        assert($request instanceof CLIRequest || $request instanceof IncomingRequest);

        $response = Services::response();

        $cachedResponse = $this->pageCache->get($request, $response);

        if ($cachedResponse instanceof ResponseInterface) {
            return $cachedResponse;
        }
    }

    /**
     * Cache the page.
     *
     * @param array|null $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        assert($request instanceof CLIRequest || $request instanceof IncomingRequest);

        if (
            ! $response instanceof DownloadResponse
            && ! $response instanceof RedirectResponse
        ) {
            // Cache it without the performance metrics replaced
            // so that we can have live speed updates along the way.
            // Must be run after filters to preserve the Response headers.
            $this->pageCache->make($request, $response);
        }
    }
}
