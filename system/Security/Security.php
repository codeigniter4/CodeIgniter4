<?php

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
use CodeIgniter\Security\CSRF\CSRFConfig;
use CodeIgniter\Security\CSRF\CSRFCookie;
use CodeIgniter\Security\CSRF\TmpCookieConfig;
use CodeIgniter\Security\Exceptions\SecurityException;
use Config\App;
use Config\Cookie as CookieConfig;
use Config\Security as SecurityConfig;

/**
 * Class Security
 *
 * Provides methods that help protect your site against
 * Cross-Site Request Forgery attacks.
 */
class Security implements SecurityInterface
{
    /**
     * @var CSRFCookie
     */
    private $csrf;

    /**
     * Constructor.
     *
     * Stores our configuration and fires off the init() method to setup
     * initial state.
     */
    public function __construct(App $config)
    {
        /** @var SecurityConfig|null $configSecurity */
        $configSecurity = config('Security');

        $csrfConfig = new CSRFConfig($configSecurity);

        /** @var CookieConfig|null $cookieConfig */
        $cookieConfig = config('Cookie');

        $tmpCookieConfig = new TmpCookieConfig();

        // If `Config/Cookie.php` does not exist
        if ($cookieConfig === null) {
            $tmpCookieConfig->prefix = $config->cookiePrefix;
        } else {
            foreach (get_object_vars($cookieConfig) as $property => $value) {
                $tmpCookieConfig->{$property} = $value;
            }
        }

        // @TODO If `Config/Security.php` and `Config/Cookie.php` surely exist,
        // the signature would be CSRFCookie(SecurityConfig, CookieConfig).
        $this->csrf = new CSRFCookie($csrfConfig, $tmpCookieConfig);
    }

    /**
     * @internal for test only
     */
    public function setCsrf(CSRFCookie $csrf): void
    {
        $this->csrf = $csrf;
    }

    /**
     * CSRF Verify
     *
     * @throws SecurityException
     *
     * @return $this|false
     *
     * @deprecated Use `CodeIgniter\Security\Security::verify()` instead of using this method.
     *
     * @codeCoverageIgnore
     */
    public function CSRFVerify(RequestInterface $request)
    {
        return $this->verify($request);
    }

    /**
     * Returns the CSRF Hash.
     *
     * @deprecated Use `CodeIgniter\Security\Security::getHash()` instead of using this method.
     *
     * @codeCoverageIgnore
     */
    public function getCSRFHash(): ?string
    {
        return $this->getHash();
    }

    /**
     * Returns the CSRF Token Name.
     *
     * @deprecated Use `CodeIgniter\Security\Security::getTokenName()` instead of using this method.
     *
     * @codeCoverageIgnore
     */
    public function getCSRFTokenName(): string
    {
        return $this->getTokenName();
    }

    /**
     * CSRF Verify
     *
     * @throws SecurityException
     *
     * @return $this|false
     */
    public function verify(RequestInterface $request)
    {
        if ($this->csrf->verify($request)) {
            return $this;
        }

        return false;
    }

    /**
     * Returns the CSRF Hash.
     */
    public function getHash(): ?string
    {
        return $this->csrf->getHash();
    }

    /**
     * Returns the CSRF Token Name.
     */
    public function getTokenName(): string
    {
        return $this->csrf->getTokenName();
    }

    /**
     * Returns the CSRF Header Name.
     */
    public function getHeaderName(): string
    {
        return $this->csrf->getHeaderName();
    }

    /**
     * Returns the CSRF Cookie Name.
     */
    public function getCookieName(): string
    {
        return $this->csrf->getCookieName();
    }

    /**
     * Check if CSRF cookie is expired.
     *
     * @deprecated
     *
     * @codeCoverageIgnore
     */
    public function isExpired(): bool
    {
        return $this->csrf->isExpired();
    }

    /**
     * Check if request should be redirect on failure.
     */
    public function shouldRedirect(): bool
    {
        return $this->csrf->shouldRedirect();
    }

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
     * @param string $str          Input file name
     * @param bool   $relativePath Whether to preserve paths
     */
    public function sanitizeFilename(string $str, bool $relativePath = false): string
    {
        // List of sanitize filename strings
        $bad = [
            '../',
            '<!--',
            '-->',
            '<',
            '>',
            "'",
            '"',
            '&',
            '$',
            '#',
            '{',
            '}',
            '[',
            ']',
            '=',
            ';',
            '?',
            '%20',
            '%22',
            '%3c',
            '%253c',
            '%3e',
            '%0e',
            '%28',
            '%29',
            '%2528',
            '%26',
            '%24',
            '%3f',
            '%3b',
            '%3d',
        ];

        if (! $relativePath) {
            $bad[] = './';
            $bad[] = '/';
        }

        $str = remove_invisible_characters($str, false);

        do {
            $old = $str;
            $str = str_replace($bad, '', $str);
        } while ($old !== $str);

        return stripslashes($str);
    }
}
