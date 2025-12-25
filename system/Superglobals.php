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
    /**
     * @var array<string, array|float|int|string>
     */
    private array $server;

    /**
     * @var array<string, array|string>
     */
    private array $get;

    /**
     * @var array<string, array|string>
     */
    private array $post;

    /**
     * @var array<string, array|string>
     */
    private array $cookie;

    /**
     * @var array<string, array|string>
     */
    private array $request;

    /**
     * @var array<string, array{name: list<string>|string, type: list<string>|string, tmp_name: list<string>|string, error: int|list<int>, size: int|list<int>, full_path?: list<string>|string}>
     */
    private array $files;

    /**
     * @param array<string, array|float|int|string>|null                                                                                                                                                 $server
     * @param array<string, array|string>|null                                                                                                                                                           $get
     * @param array<string, array|string>|null                                                                                                                                                           $post
     * @param array<string, array|string>|null                                                                                                                                                           $cookie
     * @param array<string, array|string>|null                                                                                                                                                           $request
     * @param array<string, array{name: list<string>|string, type: list<string>|string, tmp_name: list<string>|string, error: int|list<int>, size: int|list<int>, full_path?: list<string>|string}>|null $files
     */
    public function __construct(
        ?array $server = null,
        ?array $get = null,
        ?array $post = null,
        ?array $cookie = null,
        ?array $request = null,
        ?array $files = null,
    ) {
        $this->server  = $server ?? $_SERVER;
        $this->get     = $get ?? $_GET;
        $this->post    = $post ?? $_POST;
        $this->cookie  = $cookie ?? $_COOKIE;
        $this->request = $request ?? $_REQUEST;
        $this->files   = $files ?? $_FILES;
    }

    /**
     * Get a value from $_SERVER
     *
     * @return array<array-key, mixed>|float|int|string|null
     */
    public function server(string $key): array|float|int|string|null
    {
        return $this->server[$key] ?? null;
    }

    /**
     * Set a value in $_SERVER
     *
     * @param array<array-key, mixed>|float|int|string $value
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
     *
     * @return array<string, array|float|int|string>
     */
    public function getServerArray(): array
    {
        return $this->server;
    }

    /**
     * Set the entire $_SERVER array
     *
     * @param array<string, array|float|int|string> $array
     */
    public function setServerArray(array $array): void
    {
        $this->server = $array;
        $_SERVER      = $array;
    }

    /**
     * Get a value from $_GET
     *
     * @return array<array-key, mixed>|string|null
     */
    public function get(string $key): array|string|null
    {
        return $this->get[$key] ?? null;
    }

    /**
     * Set a value in $_GET
     *
     * @param array<array-key, mixed>|string $value
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
     *
     * @return array<string, array|string>
     */
    public function getGetArray(): array
    {
        return $this->get;
    }

    /**
     * Set the entire $_GET array
     *
     * @param array<string, array|string> $array
     */
    public function setGetArray(array $array): void
    {
        $this->get = $array;
        $_GET      = $array;
    }

    /**
     * Get a value from $_POST
     *
     * @return array<array-key, mixed>|string|null
     */
    public function post(string $key): array|string|null
    {
        return $this->post[$key] ?? null;
    }

    /**
     * Set a value in $_POST
     *
     * @param array<array-key, mixed>|string $value
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
     *
     * @return array<string, array|string>
     */
    public function getPostArray(): array
    {
        return $this->post;
    }

    /**
     * Set the entire $_POST array
     *
     * @param array<string, array|string> $array
     */
    public function setPostArray(array $array): void
    {
        $this->post = $array;
        $_POST      = $array;
    }

    /**
     * Get a value from $_COOKIE
     *
     * @return array<array-key, mixed>|string|null
     */
    public function cookie(string $key): array|string|null
    {
        return $this->cookie[$key] ?? null;
    }

    /**
     * Set a value in $_COOKIE
     *
     * @param array<array-key, mixed>|string $value
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
     *
     * @return array<string, array|string>
     */
    public function getCookieArray(): array
    {
        return $this->cookie;
    }

    /**
     * Set the entire $_COOKIE array
     *
     * @param array<string, array|string> $array
     */
    public function setCookieArray(array $array): void
    {
        $this->cookie = $array;
        $_COOKIE      = $array;
    }

    /**
     * Get a value from $_REQUEST
     *
     * @return array<array-key, mixed>|string|null
     */
    public function request(string $key): array|string|null
    {
        return $this->request[$key] ?? null;
    }

    /**
     * Set a value in $_REQUEST
     *
     * @param array<array-key, mixed>|string $value
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
     *
     * @return array<string, array|string>
     */
    public function getRequestArray(): array
    {
        return $this->request;
    }

    /**
     * Set the entire $_REQUEST array
     *
     * @param array<string, array|string> $array
     */
    public function setRequestArray(array $array): void
    {
        $this->request = $array;
        $_REQUEST      = $array;
    }

    /**
     * Get all $_FILES values
     *
     * @return array<string, array{name: list<string>|string, type: list<string>|string, tmp_name: list<string>|string, error: int|list<int>, size: int|list<int>, full_path?: list<string>|string}>
     */
    public function getFilesArray(): array
    {
        return $this->files;
    }

    /**
     * Set the entire $_FILES array
     *
     * @param array<string, array{name: list<string>|string, type: list<string>|string, tmp_name: list<string>|string, error: int|list<int>, size: int|list<int>, full_path?: list<string>|string}> $array
     */
    public function setFilesArray(array $array): void
    {
        $this->files = $array;
        $_FILES      = $array;
    }

    /**
     * Get a superglobal array by name
     *
     * @param string $name The superglobal name (server, get, post, cookie, request, files)
     *
     * @return array<string, array|float|int|string>
     */
    public function getGlobalArray(string $name): array
    {
        return match ($name) {
            'server'  => $this->server,
            'get'     => $this->get,
            'post'    => $this->post,
            'cookie'  => $this->cookie,
            'request' => $this->request,
            'files'   => $this->files,
            default   => [],
        };
    }

    /**
     * Set a superglobal array by name
     *
     * @param string                                $name  The superglobal name (server, get, post, cookie, request, files)
     * @param array<string, array|float|int|string> $array The array to set
     */
    public function setGlobalArray(string $name, array $array): void
    {
        match ($name) {
            'server'  => $this->setServerArray($array),
            'get'     => $this->setGetArray($array),
            'post'    => $this->setPostArray($array),
            'cookie'  => $this->setCookieArray($array),
            'request' => $this->setRequestArray($array),
            'files'   => $this->setFilesArray($array),
            default   => null,
        };
    }
}
