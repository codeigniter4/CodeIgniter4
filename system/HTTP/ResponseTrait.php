<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;
use CodeIgniter\Cookie\Exceptions\CookieException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Pager\PagerInterface;
use Config\Services;
use DateTime;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Request Trait
 *
 * Additional methods to make a PSR-7 Response class
 * compliant with the framework's own ResponseInterface.
 *
 * @property array $statusCodes
 *
 * @see https://github.com/php-fig/http-message/blob/master/src/ResponseInterface.php
 */
trait ResponseTrait
{
    /**
     * Whether Content Security Policy is being enforced.
     *
     * @var bool
     */
    protected $CSPEnabled = false;

    /**
     * Content security policy handler
     *
     * @var ContentSecurityPolicy
     */
    public $CSP;

    /**
     * CookieStore instance.
     *
     * @var CookieStore
     */
    protected $cookieStore;

    /**
     * Set a cookie name prefix if you need to avoid collisions
     *
     * @var string
     *
     * @deprecated Use the dedicated Cookie class instead.
     */
    protected $cookiePrefix = '';

    /**
     * Set to .your-domain.com for site-wide cookies
     *
     * @var string
     *
     * @deprecated Use the dedicated Cookie class instead.
     */
    protected $cookieDomain = '';

    /**
     * Typically will be a forward slash
     *
     * @var string
     *
     * @deprecated Use the dedicated Cookie class instead.
     */
    protected $cookiePath = '/';

    /**
     * Cookie will only be set if a secure HTTPS connection exists.
     *
     * @var bool
     *
     * @deprecated Use the dedicated Cookie class instead.
     */
    protected $cookieSecure = false;

    /**
     * Cookie will only be accessible via HTTP(S) (no javascript)
     *
     * @var bool
     *
     * @deprecated Use the dedicated Cookie class instead.
     */
    protected $cookieHTTPOnly = false;

    /**
     * Cookie SameSite setting
     *
     * @var string
     *
     * @deprecated Use the dedicated Cookie class instead.
     */
    protected $cookieSameSite = Cookie::SAMESITE_LAX;

    /**
     * Stores all cookies that were set in the response.
     *
     * @var array
     *
     * @deprecated Use the dedicated Cookie class instead.
     */
    protected $cookies = [];

    /**
     * Type of format the body is in.
     * Valid: html, json, xml
     *
     * @var string
     */
    protected $bodyFormat = 'html';

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, will default recommended reason phrase for
     * the response's status code.
     *
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @param int    $code   The 3-digit integer result code to set.
     * @param string $reason The reason phrase to use with the
     *                       provided status code; if none is provided, will
     *                       default to the IANA name.
     *
     * @throws HTTPException For invalid status code arguments.
     *
     * @return $this
     */
    public function setStatusCode(int $code, string $reason = '')
    {
        // Valid range?
        if ($code < 100 || $code > 599) {
            throw HTTPException::forInvalidStatusCode($code);
        }

        // Unknown and no message?
        if (! array_key_exists($code, static::$statusCodes) && empty($reason)) {
            throw HTTPException::forUnkownStatusCode($code);
        }

        $this->statusCode = $code;

        $this->reason = ! empty($reason) ? $reason : static::$statusCodes[$code];

        return $this;
    }

    //--------------------------------------------------------------------
    // Convenience Methods
    //--------------------------------------------------------------------

    /**
     * Sets the date header
     *
     * @param DateTime $date
     *
     * @return Response
     */
    public function setDate(DateTime $date)
    {
        $date->setTimezone(new DateTimeZone('UTC'));

        $this->setHeader('Date', $date->format('D, d M Y H:i:s') . ' GMT');

        return $this;
    }

    /**
     * Set the Link Header
     *
     * @param PagerInterface $pager
     *
     * @see http://tools.ietf.org/html/rfc5988
     *
     * @return Response
     *
     * @todo Recommend moving to Pager
     */
    public function setLink(PagerInterface $pager)
    {
        $links = '';

        if ($previous = $pager->getPreviousPageURI()) {
            $links .= '<' . $pager->getPageURI($pager->getFirstPage()) . '>; rel="first",';
            $links .= '<' . $previous . '>; rel="prev"';
        }

        if (($next = $pager->getNextPageURI()) && $previous) {
            $links .= ',';
        }

        if ($next) {
            $links .= '<' . $next . '>; rel="next",';
            $links .= '<' . $pager->getPageURI($pager->getLastPage()) . '>; rel="last"';
        }

        $this->setHeader('Link', $links);

        return $this;
    }

    /**
     * Sets the Content Type header for this response with the mime type
     * and, optionally, the charset.
     *
     * @param string $mime
     * @param string $charset
     *
     * @return Response
     */
    public function setContentType(string $mime, string $charset = 'UTF-8')
    {
        // add charset attribute if not already there and provided as parm
        if ((strpos($mime, 'charset=') < 1) && ! empty($charset)) {
            $mime .= '; charset=' . $charset;
        }

        $this->removeHeader('Content-Type'); // replace existing content type
        $this->setHeader('Content-Type', $mime);

        return $this;
    }

    /**
     * Converts the $body into JSON and sets the Content Type header.
     *
     * @param array|string $body
     * @param bool         $unencoded
     *
     * @return $this
     */
    public function setJSON($body, bool $unencoded = false)
    {
        $this->body = $this->formatBody($body, 'json' . ($unencoded ? '-unencoded' : ''));

        return $this;
    }

    /**
     * Returns the current body, converted to JSON is it isn't already.
     *
     * @throws InvalidArgumentException If the body property is not array.
     *
     * @return mixed|string
     */
    public function getJSON()
    {
        $body = $this->body;

        if ($this->bodyFormat !== 'json') {
            $body = Services::format()->getFormatter('application/json')->format($body);
        }

        return $body ?: null;
    }

    /**
     * Converts $body into XML, and sets the correct Content-Type.
     *
     * @param array|string $body
     *
     * @return $this
     */
    public function setXML($body)
    {
        $this->body = $this->formatBody($body, 'xml');

        return $this;
    }

    /**
     * Retrieves the current body into XML and returns it.
     *
     * @throws InvalidArgumentException If the body property is not array.
     *
     * @return mixed|string
     */
    public function getXML()
    {
        $body = $this->body;

        if ($this->bodyFormat !== 'xml') {
            $body = Services::format()->getFormatter('application/xml')->format($body);
        }

        return $body;
    }

    /**
     * Handles conversion of the of the data into the appropriate format,
     * and sets the correct Content-Type header for our response.
     *
     * @param array|string $body
     * @param string       $format Valid: json, xml
     *
     * @throws InvalidArgumentException If the body property is not string or array.
     *
     * @return mixed
     */
    protected function formatBody($body, string $format)
    {
        $this->bodyFormat = ($format === 'json-unencoded' ? 'json' : $format);
        $mime             = "application/{$this->bodyFormat}";
        $this->setContentType($mime);

        // Nothing much to do for a string...
        if (! is_string($body) || $format === 'json-unencoded') {
            $body = Services::format()->getFormatter($mime)->format($body);
        }

        return $body;
    }

    //--------------------------------------------------------------------
    // Cache Control Methods
    //
    // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
    //--------------------------------------------------------------------

    /**
     * Sets the appropriate headers to ensure this response
     * is not cached by the browsers.
     *
     * @return Response
     *
     * @todo Recommend researching these directives, might need: 'private', 'no-transform', 'no-store', 'must-revalidate'
     *
     * @see DownloadResponse::noCache()
     */
    public function noCache()
    {
        $this->removeHeader('Cache-control');
        $this->setHeader('Cache-control', ['no-store', 'max-age=0', 'no-cache']);

        return $this;
    }

    /**
     * A shortcut method that allows the developer to set all of the
     * cache-control headers in one method call.
     *
     * The options array is used to provide the cache-control directives
     * for the header. It might look something like:
     *
     *      $options = [
     *          'max-age'  => 300,
     *          's-maxage' => 900
     *          'etag'     => 'abcde',
     *      ];
     *
     * Typical options are:
     *  - etag
     *  - last-modified
     *  - max-age
     *  - s-maxage
     *  - private
     *  - public
     *  - must-revalidate
     *  - proxy-revalidate
     *  - no-transform
     *
     * @param array $options
     *
     * @return Response
     */
    public function setCache(array $options = [])
    {
        if (empty($options)) {
            return $this;
        }

        $this->removeHeader('Cache-Control');
        $this->removeHeader('ETag');

        // ETag
        if (isset($options['etag'])) {
            $this->setHeader('ETag', $options['etag']);
            unset($options['etag']);
        }

        // Last Modified
        if (isset($options['last-modified'])) {
            $this->setLastModified($options['last-modified']);

            unset($options['last-modified']);
        }

        $this->setHeader('Cache-control', $options);

        return $this;
    }

    /**
     * Sets the Last-Modified date header.
     *
     * $date can be either a string representation of the date or,
     * preferably, an instance of DateTime.
     *
     * @param DateTime|string $date
     *
     * @return Response
     */
    public function setLastModified($date)
    {
        if ($date instanceof DateTime) {
            $date->setTimezone(new DateTimeZone('UTC'));
            $this->setHeader('Last-Modified', $date->format('D, d M Y H:i:s') . ' GMT');
        } elseif (is_string($date)) {
            $this->setHeader('Last-Modified', $date);
        }

        return $this;
    }

    //--------------------------------------------------------------------
    // Output Methods
    //--------------------------------------------------------------------

    /**
     * Sends the output to the browser.
     *
     * @return Response
     */
    public function send()
    {
        // If we're enforcing a Content Security Policy,
        // we need to give it a chance to build out it's headers.
        if ($this->CSPEnabled === true) {
            $this->CSP->finalize($this);
        } else {
            $this->body = str_replace(['{csp-style-nonce}', '{csp-script-nonce}'], '', $this->body);
        }

        $this->sendHeaders();
        $this->sendCookies();
        $this->sendBody();

        return $this;
    }

    /**
     * Sends the headers of this HTTP request to the browser.
     *
     * @return Response
     */
    public function sendHeaders()
    {
        // Have the headers already been sent?
        if ($this->pretend || headers_sent()) {
            return $this;
        }

        // Per spec, MUST be sent with each request, if possible.
        // http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html
        if (! isset($this->headers['Date']) && PHP_SAPI !== 'cli-server') {
            $this->setDate(DateTime::createFromFormat('U', (string) time()));
        }

        // HTTP Status
        header(sprintf('HTTP/%s %s %s', $this->getProtocolVersion(), $this->getStatusCode(), $this->getReason()), true, $this->getStatusCode());

        // Send all of our headers
        foreach (array_keys($this->getHeaders()) as $name) {
            header($name . ': ' . $this->getHeaderLine($name), false, $this->getStatusCode());
        }

        return $this;
    }

    /**
     * Sends the Body of the message to the browser.
     *
     * @return Response
     */
    public function sendBody()
    {
        echo $this->body;

        return $this;
    }

    /**
     * Perform a redirect to a new URL, in two flavors: header or location.
     *
     * @param string $uri    The URI to redirect to
     * @param string $method
     * @param int    $code   The type of redirection, defaults to 302
     *
     * @throws HTTPException For invalid status code.
     *
     * @return $this
     */
    public function redirect(string $uri, string $method = 'auto', ?int $code = null)
    {
        // Assume 302 status code response; override if needed
        if (empty($code)) {
            $code = 302;
        }

        // IIS environment likely? Use 'refresh' for better compatibility
        if ($method === 'auto' && isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false) {
            $method = 'refresh';
        }

        // override status code for HTTP/1.1 & higher
        // reference: http://en.wikipedia.org/wiki/Post/Redirect/Get
        if (isset($_SERVER['SERVER_PROTOCOL'], $_SERVER['REQUEST_METHOD']) && $this->getProtocolVersion() >= 1.1 && $method !== 'refresh') {
            $code = ($_SERVER['REQUEST_METHOD'] !== 'GET') ? 303 : ($code === 302 ? 307 : $code);
        }

        switch ($method) {
            case 'refresh':
                $this->setHeader('Refresh', '0;url=' . $uri);
                break;

            default:
                $this->setHeader('Location', $uri);
                break;
        }

        $this->setStatusCode($code);

        return $this;
    }

    /**
     * Set a cookie
     *
     * Accepts an arbitrary number of binds (up to 7) or an associative
     * array in the first parameter containing all the values.
     *
     * @param array|string $name     Cookie name or array containing binds
     * @param string       $value    Cookie value
     * @param string       $expire   Cookie expiration time in seconds
     * @param string       $domain   Cookie domain (e.g.: '.yourdomain.com')
     * @param string       $path     Cookie path (default: '/')
     * @param string       $prefix   Cookie name prefix
     * @param bool         $secure   Whether to only transfer cookies via SSL
     * @param bool         $httponly Whether only make the cookie accessible via HTTP (no javascript)
     * @param string|null  $samesite
     *
     * @return $this
     */
    public function setCookie(
        $name,
        $value = '',
        $expire = '',
        $domain = '',
        $path = '/',
        $prefix = '',
        $secure = false,
        $httponly = false,
        $samesite = null
    ) {
        if (is_array($name)) {
            // always leave 'name' in last place, as the loop will break otherwise, due to $$item
            foreach (['samesite', 'value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name'] as $item) {
                if (isset($name[$item])) {
                    ${$item} = $name[$item];
                }
            }
        }

        if (is_numeric($expire)) {
            $expire = $expire > 0 ? time() + $expire : 0;
        }

        $cookie = new Cookie($name, $value, [
            'expires'  => $expire ?: 0,
            'domain'   => $domain,
            'path'     => $path,
            'prefix'   => $prefix,
            'secure'   => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite ?? '',
        ]);

        $this->cookieStore = $this->cookieStore->put($cookie);

        return $this;
    }

    /**
     * Returns the `CookieStore` instance.
     *
     * @return CookieStore
     */
    public function getCookieStore()
    {
        return $this->cookieStore;
    }

    /**
     * Checks to see if the Response has a specified cookie or not.
     *
     * @param string      $name
     * @param string|null $value
     * @param string      $prefix
     *
     * @return bool
     */
    public function hasCookie(string $name, ?string $value = null, string $prefix = ''): bool
    {
        $prefix = $prefix ?: Cookie::setDefaults()['prefix']; // to retain BC

        return $this->cookieStore->has($name, $prefix, $value);
    }

    /**
     * Returns the cookie
     *
     * @param string|null $name
     * @param string      $prefix
     *
     * @return Cookie|Cookie[]|null
     */
    public function getCookie(?string $name = null, string $prefix = '')
    {
        if ((string) $name === '') {
            return $this->cookieStore->display();
        }

        try {
            $prefix = $prefix ?: Cookie::setDefaults()['prefix']; // to retain BC

            return $this->cookieStore->get($name, $prefix);
        } catch (CookieException $e) {
            log_message('error', $e->getMessage());

            return null;
        }
    }

    /**
     * Sets a cookie to be deleted when the response is sent.
     *
     * @param string $name
     * @param string $domain
     * @param string $path
     * @param string $prefix
     *
     * @return $this
     */
    public function deleteCookie(string $name = '', string $domain = '', string $path = '/', string $prefix = '')
    {
        if ($name === '') {
            return $this;
        }

        $prefix = $prefix ?: Cookie::setDefaults()['prefix']; // to retain BC

        $prefixed = $prefix . $name;
        $store    = $this->cookieStore;
        $found    = false;

        foreach ($store as $cookie) {
            if ($cookie->getPrefixedName() === $prefixed) {
                if ($domain !== $cookie->getDomain()) {
                    continue;
                }

                if ($path !== $cookie->getPath()) {
                    continue;
                }

                $cookie = $cookie->withValue('')->withExpired();
                $found  = true;

                $this->cookieStore = $store->put($cookie);
                break;
            }
        }

        if (! $found) {
            $this->setCookie($name, '', '', $domain, $path, $prefix);
        }

        return $this;
    }

    /**
     * Returns all cookies currently set.
     *
     * @return Cookie[]
     */
    public function getCookies()
    {
        return $this->cookieStore->display();
    }

    /**
     * Actually sets the cookies.
     */
    protected function sendCookies()
    {
        if ($this->pretend) {
            return;
        }

        $this->cookieStore->dispatch();
    }

    /**
     * Force a download.
     *
     * Generates the headers that force a download to happen. And
     * sends the file to the browser.
     *
     * @param string      $filename The path to the file to send
     * @param string|null $data     The data to be downloaded
     * @param bool        $setMime  Whether to try and send the actual MIME type
     *
     * @return DownloadResponse|null
     */
    public function download(string $filename = '', $data = '', bool $setMime = false)
    {
        if ($filename === '' || $data === '') {
            return null;
        }

        $filepath = '';
        if ($data === null) {
            $filepath = $filename;
            $filename = explode('/', str_replace(DIRECTORY_SEPARATOR, '/', $filename));
            $filename = end($filename);
        }

        $response = new DownloadResponse($filename, $setMime);

        if ($filepath !== '') {
            $response->setFilePath($filepath);
        } elseif ($data !== null) {
            $response->setBinary($data);
        }

        return $response;
    }
}
