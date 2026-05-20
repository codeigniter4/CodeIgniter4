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
use CodeIgniter\HTTP\Method;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Security\Security;
use Config\Security as SecurityConfig;

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
     * @return RedirectResponse|null
     *
     * @throws SecurityException
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return null;
        }

        /** @var Security $security */
        $security = service('security');

        try {
            $security->verify($request);
        } catch (SecurityException $e) {
            if ($security->shouldRedirect() && ! $request->isAJAX()) {
                $response = redirect()->back()->with('error', $e->getMessage());
                $this->addFetchMetadataVaryHeader($request, $response);

                return $response;
            }

            $this->addFetchMetadataVaryHeader($request, service('response'));

            throw $e;
        }

        $this->addFetchMetadataVaryHeader($request, service('response'));

        return null;
    }

    /**
     * We don't have anything to do here.
     *
     * @param list<string>|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function addFetchMetadataVaryHeader(IncomingRequest $request, ResponseInterface $response): void
    {
        $config           = config(SecurityConfig::class);
        $useFetchMetadata = ($config->csrfUseFetchMetadata ?? false) === true; // @phpstan-ignore nullCoalesce.property
        $isUnsafeMethod   = in_array($request->getMethod(), [Method::POST, Method::PUT, Method::DELETE, Method::PATCH], true);

        if ($useFetchMetadata && $isUnsafeMethod) {
            $response->appendHeader('Vary', 'Sec-Fetch-Site');
        }
    }
}
