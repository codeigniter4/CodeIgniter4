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

namespace CodeIgniter;

/**
 * Superglobals manipulation.
 *
 * Provides a clean API for accessing and manipulating PHP superglobals
 * with support for testing and backward compatibility.
 *
 * Note on return types:
 * - $_SERVER can contain int (argc, REQUEST_TIME) or float (REQUEST_TIME_FLOAT)
 * - $_SERVER['argv'] is an array
 * - $_GET, $_POST, $_REQUEST can contain nested arrays from query params like ?foo[bar]=value
 * - $_COOKIE typically contains strings but can have arrays with cookie[key] notation
 *
 * @internal
 * @see \CodeIgniter\SuperglobalsTest
 */
final class Superglobals
{
    private array $server;
    private array $get;
    private array $post;
    private array $cookie;
    private array $request;

    public function __construct(
        ?array $server = null,
        ?array $get = null,
        ?array $post = null,
        ?array $cookie = null,
        ?array $request = null,
    ) {
        $this->server  = $server ?? $_SERVER;
        $this->get     = $get ?? $_GET;
        $this->post    = $post ?? $_POST;
        $this->cookie  = $cookie ?? $_COOKIE;
        $this->request = $request ?? $_REQUEST;
    }

    /**
     * Get a value from $_SERVER
     */
    public function server(string $key): array|float|int|string|null
    {
        return $this->server[$key] ?? null;
    }

    /**
     * Set a value in $_SERVER
     */
    public function setServer(string $key, array|float|int|string $value): void
    {
        $this->server[$key] = $value;
        $_SERVER[$key]      = $value;
    }

    /**
     * Remove a key from $_SERVER
     */
    public function unsetServer(string $key): void
    {
        unset($this->server[$key], $_SERVER[$key]);
    }

    /**
     * Get all $_SERVER values
     */
    public function getServerArray(): array
    {
        return $this->server;
    }

    /**
     * Set the entire $_SERVER array
     */
    public function setServerArray(array $array): void
    {
        $this->server = $array;
        $_SERVER      = $array;
    }

    /**
     * Get a value from $_GET
     */
    public function get(string $key): array|string|null
    {
        return $this->get[$key] ?? null;
    }

    /**
     * Set a value in $_GET
     */
    public function setGet(string $key, array|string $value): void
    {
        $this->get[$key] = $value;
        $_GET[$key]      = $value;
    }

    /**
     * Remove a key from $_GET
     */
    public function unsetGet(string $key): void
    {
        unset($this->get[$key], $_GET[$key]);
    }

    /**
     * Get all $_GET values
     */
    public function getGetArray(): array
    {
        return $this->get;
    }

    /**
     * Set the entire $_GET array
     */
    public function setGetArray(array $array): void
    {
        $this->get = $array;
        $_GET      = $array;
    }

    /**
     * Get a value from $_POST
     */
    public function post(string $key): array|string|null
    {
        return $this->post[$key] ?? null;
    }

    /**
     * Set a value in $_POST
     */
    public function setPost(string $key, array|string $value): void
    {
        $this->post[$key] = $value;
        $_POST[$key]      = $value;
    }

    /**
     * Remove a key from $_POST
     */
    public function unsetPost(string $key): void
    {
        unset($this->post[$key], $_POST[$key]);
    }

    /**
     * Get all $_POST values
     */
    public function getPostArray(): array
    {
        return $this->post;
    }

    /**
     * Set the entire $_POST array
     */
    public function setPostArray(array $array): void
    {
        $this->post = $array;
        $_POST      = $array;
    }

    /**
     * Get a value from $_COOKIE
     */
    public function cookie(string $key): array|string|null
    {
        return $this->cookie[$key] ?? null;
    }

    /**
     * Set a value in $_COOKIE
     */
    public function setCookie(string $key, array|string $value): void
    {
        $this->cookie[$key] = $value;
        $_COOKIE[$key]      = $value;
    }

    /**
     * Remove a key from $_COOKIE
     */
    public function unsetCookie(string $key): void
    {
        unset($this->cookie[$key], $_COOKIE[$key]);
    }

    /**
     * Get all $_COOKIE values
     */
    public function getCookieArray(): array
    {
        return $this->cookie;
    }

    /**
     * Set the entire $_COOKIE array
     */
    public function setCookieArray(array $array): void
    {
        $this->cookie = $array;
        $_COOKIE      = $array;
    }

    /**
     * Get a value from $_REQUEST
     */
    public function request(string $key): array|string|null
    {
        return $this->request[$key] ?? null;
    }

    /**
     * Set a value in $_REQUEST
     */
    public function setRequest(string $key, array|string $value): void
    {
        $this->request[$key] = $value;
        $_REQUEST[$key]      = $value;
    }

    /**
     * Remove a key from $_REQUEST
     */
    public function unsetRequest(string $key): void
    {
        unset($this->request[$key], $_REQUEST[$key]);
    }

    /**
     * Get all $_REQUEST values
     */
    public function getRequestArray(): array
    {
        return $this->request;
    }

    /**
     * Set the entire $_REQUEST array
     */
    public function setRequestArray(array $array): void
    {
        $this->request = $array;
        $_REQUEST      = $array;
    }

    /**
     * Get a superglobal array by name
     *
     * @param string $name The superglobal name (server, get, post, cookie, request)
     */
    public function getGlobalArray(string $name): array
    {
        return match ($name) {
            'server'  => $this->server,
            'get'     => $this->get,
            'post'    => $this->post,
            'cookie'  => $this->cookie,
            'request' => $this->request,
            default   => [],
        };
    }

    /**
     * Set a superglobal array by name
     *
     * @param string $name  The superglobal name (server, get, post, cookie, request)
     * @param array  $array The array to set
     */
    public function setGlobalArray(string $name, array $array): void
    {
        match ($name) {
            'server'  => $this->setServerArray($array),
            'get'     => $this->setGetArray($array),
            'post'    => $this->setPostArray($array),
            'cookie'  => $this->setCookieArray($array),
            'request' => $this->setRequestArray($array),
            default   => null,
        };
    }
}
