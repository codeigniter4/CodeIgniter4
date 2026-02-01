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

use CodeIgniter\Exceptions\InvalidArgumentException;

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
 * Note: $_FILES only supports array operations (getFilesArray/setFilesArray).
 * Individual key operations are not provided as files don't change after request initialization.
 *
 * @phpstan-type server_items  array<array-key, mixed>|float|int|string
 * @phpstan-type get_items     array<array-key, mixed>|string
 * @phpstan-type post_items    array<array-key, mixed>|string
 * @phpstan-type cookie_items  array<array-key, mixed>|string
 * @phpstan-type files_items   array<array-key, mixed>
 * @phpstan-type request_items array<array-key, mixed>|string
 *
 * @internal
 * @see \CodeIgniter\SuperglobalsTest
 */
final class Superglobals
{
    /**
     * @var array<string, server_items>
     */
    private array $server = [];

    /**
     * @var array<string, get_items>
     */
    private array $get = [];

    /**
     * @var array<string, post_items>
     */
    private array $post = [];

    /**
     * @var array<string, cookie_items>
     */
    private array $cookie = [];

    /**
     * @var array<string, files_items>
     */
    private array $files = [];

    /**
     * @var array<string, request_items>
     */
    private array $request = [];

    /**
     * @param array<string, server_items>|null  $server
     * @param array<string, get_items>|null     $get
     * @param array<string, post_items>|null    $post
     * @param array<string, cookie_items>|null  $cookie
     * @param array<string, files_items>|null   $files
     * @param array<string, request_items>|null $request
     */
    public function __construct(
        ?array $server = null,
        ?array $get = null,
        ?array $post = null,
        ?array $cookie = null,
        ?array $files = null,
        ?array $request = null,
    ) {
        $this
            ->setServerArray($server ?? $_SERVER)
            ->setGetArray($get ?? $_GET)
            ->setPostArray($post ?? $_POST)
            ->setCookieArray($cookie ?? $_COOKIE)
            ->setFilesArray($files ?? $_FILES)
            ->setRequestArray($request ?? $_REQUEST);
    }

    /**
     * Get a value from $_SERVER.
     *
     * @param server_items|null $default
     *
     * @return server_items|null
     */
    public function server(string $key, mixed $default = null): array|float|int|string|null
    {
        return $this->server[$key] ?? $default;
    }

    /**
     * Set a value in $_SERVER.
     *
     * @param server_items $value
     */
    public function setServer(string $key, array|float|int|string $value): self
    {
        $this->server[$key] = $value;
        $_SERVER[$key]      = $value;

        return $this;
    }

    /**
     * Remove a key from $_SERVER.
     */
    public function unsetServer(string $key): self
    {
        unset($this->server[$key], $_SERVER[$key]);

        return $this;
    }

    /**
     * Get all $_SERVER values.
     *
     * @return array<string, server_items>
     */
    public function getServerArray(): array
    {
        return $this->server;
    }

    /**
     * Set the entire $_SERVER array.
     *
     * @param array<string, server_items> $array
     */
    public function setServerArray(array $array): self
    {
        $this->server = $array;
        $_SERVER      = $array;

        return $this;
    }

    /**
     * Get a value from $_GET.
     *
     * @param get_items|null $default
     *
     * @return get_items|null
     */
    public function get(string $key, mixed $default = null): array|string|null
    {
        return $this->get[$key] ?? $default;
    }

    /**
     * Set a value in $_GET.
     *
     * @param get_items $value
     */
    public function setGet(string $key, array|string $value): self
    {
        $this->get[$key] = $value;
        $_GET[$key]      = $value;

        return $this;
    }

    /**
     * Remove a key from $_GET.
     */
    public function unsetGet(string $key): self
    {
        unset($this->get[$key], $_GET[$key]);

        return $this;
    }

    /**
     * Get all $_GET values.
     *
     * @return array<string, get_items>
     */
    public function getGetArray(): array
    {
        return $this->get;
    }

    /**
     * Set the entire $_GET array.
     *
     * @param array<string, get_items> $array
     */
    public function setGetArray(array $array): self
    {
        $this->get = $array;
        $_GET      = $array;

        return $this;
    }

    /**
     * Get a value from $_POST.
     *
     * @param post_items|null $default
     *
     * @return post_items|null
     */
    public function post(string $key, mixed $default = null): array|string|null
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Set a value in $_POST.
     *
     * @param post_items $value
     */
    public function setPost(string $key, array|string $value): self
    {
        $this->post[$key] = $value;
        $_POST[$key]      = $value;

        return $this;
    }

    /**
     * Remove a key from $_POST.
     */
    public function unsetPost(string $key): self
    {
        unset($this->post[$key], $_POST[$key]);

        return $this;
    }

    /**
     * Get all $_POST values.
     *
     * @return array<string, post_items>
     */
    public function getPostArray(): array
    {
        return $this->post;
    }

    /**
     * Set the entire $_POST array.
     *
     * @param array<string, post_items> $array
     */
    public function setPostArray(array $array): self
    {
        $this->post = $array;
        $_POST      = $array;

        return $this;
    }

    /**
     * Get a value from $_COOKIE.
     *
     * @param cookie_items|null $default
     *
     * @return cookie_items|null
     */
    public function cookie(string $key, mixed $default = null): array|string|null
    {
        return $this->cookie[$key] ?? $default;
    }

    /**
     * Set a value in $_COOKIE.
     *
     * @param cookie_items $value
     */
    public function setCookie(string $key, array|string $value): self
    {
        $this->cookie[$key] = $value;
        $_COOKIE[$key]      = $value;

        return $this;
    }

    /**
     * Remove a key from $_COOKIE.
     */
    public function unsetCookie(string $key): self
    {
        unset($this->cookie[$key], $_COOKIE[$key]);

        return $this;
    }

    /**
     * Get all $_COOKIE values.
     *
     * @return array<string, cookie_items>
     */
    public function getCookieArray(): array
    {
        return $this->cookie;
    }

    /**
     * Set the entire $_COOKIE array.
     *
     * @param array<string, cookie_items> $array
     */
    public function setCookieArray(array $array): self
    {
        $this->cookie = $array;
        $_COOKIE      = $array;

        return $this;
    }

    /**
     * Get a value from $_REQUEST.
     *
     * @param request_items|null $default
     *
     * @return request_items|null
     */
    public function request(string $key, mixed $default = null): array|string|null
    {
        return $this->request[$key] ?? $default;
    }

    /**
     * Set a value in $_REQUEST.
     *
     * @param request_items $value
     */
    public function setRequest(string $key, array|string $value): self
    {
        $this->request[$key] = $value;
        $_REQUEST[$key]      = $value;

        return $this;
    }

    /**
     * Remove a key from $_REQUEST.
     */
    public function unsetRequest(string $key): self
    {
        unset($this->request[$key], $_REQUEST[$key]);

        return $this;
    }

    /**
     * Get all $_REQUEST values.
     *
     * @return array<string, request_items>
     */
    public function getRequestArray(): array
    {
        return $this->request;
    }

    /**
     * Set the entire $_REQUEST array.
     *
     * @param array<string, request_items> $array
     */
    public function setRequestArray(array $array): self
    {
        $this->request = $array;
        $_REQUEST      = $array;

        return $this;
    }

    /**
     * Get all $_FILES values.
     *
     * @return array<string, files_items>
     */
    public function getFilesArray(): array
    {
        return $this->files;
    }

    /**
     * Set the entire $_FILES array.
     *
     * @param array<string, files_items> $array
     */
    public function setFilesArray(array $array): self
    {
        $this->files = $array;
        $_FILES      = $array;

        return $this;
    }

    /**
     * Get a superglobal array by name.
     *
     * @param string $name The superglobal name (server, get, post, cookie, files, request)
     *
     * @return array<string, server_items>
     *
     * @throws InvalidArgumentException If the superglobal name is invalid
     */
    public function getGlobalArray(string $name): array
    {
        return match ($name) {
            'server'  => $this->server,
            'get'     => $this->get,
            'post'    => $this->post,
            'cookie'  => $this->cookie,
            'files'   => $this->files,
            'request' => $this->request,
            default   => throw new InvalidArgumentException(
                "Invalid superglobal name '{$name}'. Must be one of: server, get, post, cookie, files, request.",
            ),
        };
    }

    /**
     * Set a superglobal array by name.
     *
     * @param string                      $name  The superglobal name (server, get, post, cookie, files, request)
     * @param array<string, server_items> $array The array to set
     *
     * @throws InvalidArgumentException If the superglobal name is invalid
     */
    public function setGlobalArray(string $name, array $array): void
    {
        match ($name) {
            'server'  => $this->setServerArray($array),
            'get'     => $this->setGetArray($array),
            'post'    => $this->setPostArray($array),
            'cookie'  => $this->setCookieArray($array),
            'files'   => $this->setFilesArray($array),
            'request' => $this->setRequestArray($array),
            default   => throw new InvalidArgumentException(
                "Invalid superglobal name '{$name}'. Must be one of: server, get, post, cookie, files, request.",
            ),
        };
    }
}
