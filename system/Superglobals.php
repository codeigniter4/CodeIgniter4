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
 */
final class Superglobals
{
    public function server(string $key): ?string
    {
        return $_SERVER[$key] ?? null;
    }

    public function setServer(string $key, string $value): void
    {
        $_SERVER[$key] = $value;
    }

    public function setGetArray(array $array): void
    {
        $_GET = $array;
    }
}
