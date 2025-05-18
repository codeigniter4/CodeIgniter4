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
 * Expected behavior of a Security.
 */
interface SecurityInterface
{
    /**
     * CSRF Verify
     *
     * @return $this|false
     *
     * @throws SecurityException
     */
    public function verify(RequestInterface $request);

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

    /**
     * Sanitize Filename
     *
     * Tries to sanitize filenames in order to prevent directory traversal attempts
     * and other security threats, which is particularly useful for files that
     * were supplied via user input.
     *
     * If it is acceptable for the user input to include relative paths,
     * e.g. file/in/some/approved/folder.txt, you can set the second optional
     * parameter, $relativePath to TRUE.
     *
     * @deprecated 4.6.2 Use `sanitize_filename()` instead
     *
     * @param string $str          Input file name
     * @param bool   $relativePath Whether to preserve paths
     */
    public function sanitizeFilename(string $str, bool $relativePath = false): string;
}
