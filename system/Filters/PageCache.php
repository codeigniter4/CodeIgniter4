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

use CodeIgniter\Cache\ResponseCache;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Cache;

/**
 * Page Cache filter
 */
class PageCache implements FilterInterface
{
    private readonly ResponseCache $pageCache;

    /**
     * @var list<int>
     */
    private readonly array $cacheStatusCodes;

    public function __construct(?Cache $config = null)
    {
        $config ??= config('Cache');

        $this->pageCache        = service('responsecache');
        $this->cacheStatusCodes = $config->cacheStatusCodes ?? [];
    }

    /**
     * Checks page cache and return if found.
     *
     * @param array|null $arguments
     *
     * @return ResponseInterface|null
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        assert($request instanceof CLIRequest || $request instanceof IncomingRequest);

        $response = service('response');

        return $this->pageCache->get($request, $response);
    }

    /**
     * Cache the page.
     *
     * @param array|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        assert($request instanceof CLIRequest || $request instanceof IncomingRequest);

        if (
            ! $response instanceof DownloadResponse
            && ! $response instanceof RedirectResponse
            && ($this->cacheStatusCodes === [] || in_array($response->getStatusCode(), $this->cacheStatusCodes, true))
        ) {
            // Cache it without the performance metrics replaced
            // so that we can have live speed updates along the way.
            // Must be run after filters to preserve the Response headers.
            $this->pageCache->make($request, $response);

            return $response;
        }

        return null;
    }
}
