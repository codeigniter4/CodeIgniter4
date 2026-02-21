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

namespace CodeIgniter\Security;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Security\Exceptions\SecurityException;

/**
 * Expected behavior of a Security object providing
 * protection against CSRF attacks.
 */
interface SecurityInterface
{
    /**
     * Verify CSRF token sent with the request.
     *
     * @throws SecurityException
     */
    public function verify(RequestInterface $request): static;

    /**
     * Returns the CSRF Hash.
     */
    public function getHash(): ?string;

    /**
     * Returns the CSRF Token Name.
     */
    public function getTokenName(): string;

    /**
     * Returns the CSRF Header Name.
     */
    public function getHeaderName(): string;

    /**
     * Returns the CSRF Cookie Name.
     */
    public function getCookieName(): string;

    /**
     * Check if request should be redirect on failure.
     */
    public function shouldRedirect(): bool;
}
