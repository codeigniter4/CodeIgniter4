<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT - MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Security;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Security\Exceptions\SecurityException;
use Config\App as AppConfig;
use Config\Security as SecurityConfig;

/**
 * HTTP Security Handler.
 */
class Security
{
    /**
     * CSRF Hash
     *
     * Random hash for Cross Site Request Forgery protection cookie
     *
     * @var string|null
     */
    protected $hash = null;

    /**
     * CSRF Token Name
     *
     * Token name for Cross Site Request Forgery protection cookie.
     *
     * @var string
     */
    protected $tokenName = 'CSRFToken';

    /**
     * CSRF Header Name
     *
     * Token name for Cross Site Request Forgery protection cookie.
     *
     * @var string
     */
    protected $headerName = 'CSRFToken';

    /**
     * CSRF Cookie Name
     *
     * Cookie name for Cross Site Request Forgery protection cookie.
     *
     * @var string
     */
    protected $cookieName = 'CSRFToken';

    /**
     * CSRF Expire
     *
     * Expiration time for Cross Site Request Forgery protection cookie.
     * Defaults to two hours (in seconds).
     *
     * @var integer
     */
    protected $expire = 7200;

    /**
     * CSRF Regenerate
     *
     * true : The CSRF Token will be Regenerated CSRF Token on every request.
     * false: The CSRF will stay the same for the life of the cookie.
     *
     * @var boolean
     */
    protected $regenerate = true;

    /**
     * CSRF SameSite
     *
     * Setting for CSRF SameSite cookie token. Allowed values are:
     * - None
     * - Lax
     * - Strict
     * - ''
     *
     * Defaults to `Lax` as recommended in this link:
     *
     * @see https://portswigger.net/web-security/csrf/samesite-cookies
     *
     * @var string
     */
    protected $samesite = 'Lax';

    //--------------------------------------------------------------------

    /**
     * Constructor.
     *
     * Stores our configuration and fires off the init() method to setup
     * initial state.
     *
     * @param \Config\Security $config
     *
     * @throws \CodeIgniter\Security\Exception\SecurityException
     */
    public function __construct(SecurityConfig $config)
    {
        // Store our CSRF-related configuration.
        $this->tokenName  = $config->tokenName ?? $this->tokenName;
        $this->headerName = $config->headerName ?? $this->headerName;
        $this->cookieName = $config->cookieName ?? $this->cookieName;
        $this->expire     = $config->expire ?? $this->expire;
        $this->regenerate = $config->regenerate ?? $this->regenerate;
        $this->samesite   = $config->samesite ?? $this->samesite;

        if (! in_array(strtolower($this->samesite), ['', 'none', 'lax', 'strict'], true))
        {
            throw SecurityException::forInvalidSameSite($this->samesite);
        }

        $config = new AppConfig();

        if (isset($config->cookiePrefix))
        {
            $this->cookieName = $config->cookiePrefix . $this->cookieName;
        }

        unset($config);

        $this->generateHash();
    }

    //--------------------------------------------------------------------

    /**
     * CSRF Verify
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request
     *
     * @return $this|false
     * @throws \CodeIgniter\Security\Exception\SecurityException
     */
    public function verify(RequestInterface $request)
    {
        // If it's not a POST request we will set the CSRF cookie
        if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST')
        {
            return $this->sendCookie($request);
        }

        // Do the tokens exist in _POST, HEADER or optionally php:://input - json data
        $tokenValue = $_POST[$this->tokenName] ??
            (! is_null($request->getHeader($this->headerName)) && ! empty($request->getHeader($this->headerName)->getValue()) ?
                $request->getHeader($this->headerName)->getValue() :
                (! empty($request->getBody()) && ! empty($json = json_decode($request->getBody())) && json_last_error() === JSON_ERROR_NONE ?
                    ($json->{$this->tokenName} ?? null) :
                    null));

        // Do the tokens exist in both the _POST/POSTed JSON and _COOKIE arrays?
        if (! isset($tokenValue, $_COOKIE[$this->cookieName]) || $tokenValue !== $_COOKIE[$this->cookieName]
        ) // Do the tokens match?
        {
            throw SecurityException::forDisallowedAction();
        }

        // We kill this since we're done and we don't want to pollute the _POST array
        if (isset($_POST[$this->tokenName]))
        {
            unset($_POST[$this->tokenName]);
            $request->setGlobal('post', $_POST);
        }
        // We kill this since we're done and we don't want to pollute the JSON data
        elseif (isset($json->{$this->tokenName}))
        {
            unset($json->{$this->tokenName});
            $request->setBody(json_encode($json));
        }

        // Regenerate on every submission?
        if ($this->regenerate)
        {
            // Nothing should last forever
            $this->hash = null;
            unset($_COOKIE[$this->cookieName]);
        }

        $this->generateHash();
        $this->sendCookie($request);

        log_message('info', 'CSRF token verified');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Generates the CSRF Hash.
     *
     * @return string
     */
    protected function generateHash(): string
    {
        if (is_null($this->hash))
        {
            // If the cookie exists we will use its value.
            // We don't necessarily want to regenerate it with
            // each page load since a page could contain embedded
            // sub-pages causing this feature to fail
            if (isset($_COOKIE[$this->cookieName]) && is_string($_COOKIE[$this->cookieName]) && preg_match('#^[0-9a-f]{32}$#iS', $_COOKIE[$this->cookieName]) === 1
            )
            {
                return $this->hash = $_COOKIE[$this->cookieName];
            }

            $this->hash = bin2hex(random_bytes(16));
        }

        return $this->hash;
    }

    //--------------------------------------------------------------------

    /**
     * CSRF Send Cookie
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request
     *
     * @return Security|false
     * @codeCoverageIgnore
     */
    protected function sendCookie(RequestInterface $request)
    {
        $config = new AppConfig();

        $expire = $this->expire === 0 ? $this->expire : time() + $this->expire;
        $path   = $config->cookiePath ?? '/';
        $domain = $config->cookieDomain ?? '';
        $secure = $config->cookieSecure ?? false;

        if ($secure && ! $request->isSecure())
        {
            return false;
        }

        if (PHP_VERSION_ID < 70300)
        {
            // In PHP < 7.3.0, there is a "hacky" way to set the samesite parameter
            $samesite = '';
            if ($this->samesite !== '')
            {
                $samesite = '; samesite=' . $this->samesite;
            }

            setcookie(
                $this->cookieName,
                $this->hash,
                $expire,
                $path . $samesite,
                $domain,
                $secure,
                true // Enforce HTTP only cookie for security
            );
        }
        else
        {
            // PHP 7.3 adds another function signature allowing setting of samesite
            $params = [
                'expires'  => $expire,
                'path'     => $path,
                'domain'   => $domain,
                'secure'   => $secure,
                'httponly' => true, // Enforce HTTP only cookie for security
            ];

            if ($this->samesite !== '')
            {
                $params['samesite'] = $this->samesite;
            }

            setcookie(
                $this->cookieName,
                $this->hash,
                $params
            );
        }

        log_message('info', 'CSRF cookie sent');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Returns the CSRF Hash.
     *
     * @return string|null
     */
    public function gethash(): ?string
    {
        return $this->hash;
    }

    //--------------------------------------------------------------------

    /**
     * Returns the CSRF Token Name.
     *
     * @return string
     */
    public function getTokenName(): string
    {
        return $this->tokenName;
    }

    //--------------------------------------------------------------------

    /**
     * Returns the CSRF Header Name.
     *
     * @return string
     */
    public function getHeaderName(): string
    {
        return $this->headerName;
    }

    //--------------------------------------------------------------------

    /**
     * Returns the CSRF Cookie Name.
     *
     * @return string
     */
    public function getCookieName(): string
    {
        return $this->cookieName;
    }

    //--------------------------------------------------------------------

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
    public function sanitizeFilename(string $str, bool $relativePath = false): string
    {
        // List of sanitize filename strings
        $bad = [
            '../', '<!--', '-->', '<', '>', "'", '"', '&', '$', '#', '{', '}', '[', ']', '=', ';', '?', '%20', '%22', '%3c', '%253c', '%3e', '%0e', '%28', '%29', '%2528', '%26', '%24', '%3f', '%3b', '%3d',
        ];

        if (! $relativePath)
        {
            $bad[] = './';
            $bad[] = '/';
        }

        $str = remove_invisible_characters($str, false);

        do
        {
            $old = $str;
            $str = str_replace($bad, '', $str);
        }
        while ($old !== $str);

        return stripslashes($str);
    }
}
