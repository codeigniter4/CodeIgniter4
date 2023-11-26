<?php

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
 * @internal
 * @see \CodeIgniter\SuperglobalsTest
 */
final class Superglobals
{
    private array $server;
    private array $get;

    public function __construct(?array $server = null, ?array $get = null)
    {
        $this->server = $server ?? $_SERVER;
        $this->get    = $get ?? $_GET;
    }

    public function server(string $key): ?string
    {
        return $this->server[$key] ?? null;
    }

    public function setServer(string $key, string $value): void
    {
        $this->server[$key] = $value;
        $_SERVER[$key]      = $value;
    }

    /**
     * @return array|string|null
     */
    public function get(string $key)
    {
        return $this->get[$key] ?? null;
    }

    public function setGet(string $key, string $value): void
    {
        $this->get[$key] = $value;
        $_GET[$key]      = $value;
    }

    public function setGetArray(array $array): void
    {
        $this->get = $array;
        $_GET      = $array;
    }
}
