<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Throttle;

/**
 * Expected behavior of a Throttler
 */
interface ThrottlerInterface
{
    /**
     * Restricts the number of requests made by a single key within
     * a set number of seconds.
     *
     * Example:
     *
     *  if (! $throttler->checkIPAddress($request->ipAddress(), 60, MINUTE))
     * {
     *      die('You submitted over 60 requests within a minute.');
     * }
     *
     * @param string $key      The name to use as the "bucket" name.
     * @param int    $capacity The number of requests the "bucket" can hold
     * @param int    $seconds  The time it takes the "bucket" to completely refill
     * @param int    $cost     The number of tokens this action uses.
     *
     * @return bool
     */
    public function check(string $key, int $capacity, int $seconds, int $cost);

    /**
     * Returns the number of seconds until the next available token will
     * be released for usage.
     */
    public function getTokenTime(): int;
}
