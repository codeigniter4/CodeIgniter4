<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
	 * @param RequestInterface $request
	 *
	 * @return $this|false
	 *
	 * @throws SecurityException
	 */
	public function verify(RequestInterface $request);

	/**
	 * Returns the CSRF Hash.
	 *
	 * @return string|null
	 */
	public function getHash(): ?string;

	/**
	 * Returns the CSRF Token Name.
	 *
	 * @return string
	 */
	public function getTokenName(): string;

	/**
	 * Returns the CSRF Header Name.
	 *
	 * @return string
	 */
	public function getHeaderName(): string;

	/**
	 * Returns the CSRF Cookie Name.
	 *
	 * @return string
	 */
	public function getCookieName(): string;

	/**
	 * Check if CSRF cookie is expired.
	 *
	 * @return boolean
	 *
	 * @deprecated
	 */
	public function isExpired(): bool;

	/**
	 * Check if request should be redirect on failure.
	 *
	 * @return boolean
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
	 * parameter, $relative_path to TRUE.
	 *
	 * @param string  $str          Input file name
	 * @param boolean $relativePath Whether to preserve paths
	 *
	 * @return string
	 */
	public function sanitizeFilename(string $str, bool $relativePath = false): string;
}
