<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Throttle;

use CodeIgniter\Cache\CacheInterface;

/**
 * Class Throttler
 *
 * Uses an implementation of the Token Bucket algorithm to implement a
 * "rolling window" type of throttling that can be used for rate limiting
 * an API or any other request.
 *
 * Each "token" in the "bucket" is equivalent to a single request
 * for the purposes of this implementation.
 *
 * @see https://en.wikipedia.org/wiki/Token_bucket
 */
class Throttler implements ThrottlerInterface
{
    /**
     * Container for throttle counters.
     *
     * @var CacheInterface
     */
    protected $cache;

    /**
     * The number of seconds until the next token is available.
     *
     * @var int
     */
    protected $tokenTime = 0;

    /**
     * The prefix applied to all keys to
     * minimize potential conflicts.
     *
     * @var string
     */
    protected $prefix = 'throttler_';

    /**
     * Timestamp to use (during testing)
     *
     * @var int
     */
    protected $testTime;

    //--------------------------------------------------------------------

    /**
     * Constructor.
     *
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    //--------------------------------------------------------------------

    /**
     * Returns the number of seconds until the next available token will
     * be released for usage.
     *
     * @return int
     */
    public function getTokenTime(): int
    {
        return $this->tokenTime;
    }

    //--------------------------------------------------------------------

    /**
     * Restricts the number of requests made by a single IP address within
     * a set number of seconds.
     *
     * Example:
     *
     *  if (! $throttler->check($request->ipAddress(), 60, MINUTE))
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
     *
     * @internal param int $maxRequests
     */
    public function check(string $key, int $capacity, int $seconds, int $cost = 1): bool
    {
        $tokenName = $this->prefix . $key;

        // Check to see if the bucket has even been created yet.
        if (($tokens = $this->cache->get($tokenName)) === null) {
            // If it hasn't been created, then we'll set it to the maximum
            // capacity - 1, and save it to the cache.
            $this->cache->save($tokenName, $capacity - $cost, $seconds);
            $this->cache->save($tokenName . 'Time', time(), $seconds);

            return true;
        }

        // If $tokens > 0, then we need to replenish the bucket
        // based on how long it's been since the last update.
        $throttleTime = $this->cache->get($tokenName . 'Time');
        $elapsed      = $this->time() - $throttleTime;

        // Number of tokens to add back per second
        $rate = $capacity / $seconds;

        // How many seconds till a new token is available.
        // We must have a minimum wait of 1 second for a new token.
        // Primarily stored to allow devs to report back to users.
        $newTokenAvailable = (1 / $rate) - $elapsed;
        $this->tokenTime   = max(1, $newTokenAvailable);

        // Add tokens based up on number per second that
        // should be refilled, then checked against capacity
        // to be sure the bucket didn't overflow.
        $tokens += $rate * $elapsed;
        $tokens = $tokens > $capacity ? $capacity : $tokens;

        // If $tokens >= 1, then we are safe to perform the action, but
        // we need to decrement the number of available tokens.
        if ($tokens >= 1) {
            $this->cache->save($tokenName, $tokens - $cost, $seconds);
            $this->cache->save($tokenName . 'Time', time(), $seconds);

            return true;
        }

        return false;
    }

    /**
     * @param string $key The name of the bucket
     *
     * @return $this
     */
    public function remove(string $key): self
    {
        $tokenName = $this->prefix . $key;

        $this->cache->delete($tokenName);
        $this->cache->delete($tokenName . 'Time');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Used during testing to set the current timestamp to use.
     *
     * @param int $time
     *
     * @return $this
     */
    public function setTestTime(int $time)
    {
        $this->testTime = $time;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Return the test time, defaulting to current.
     *
     * @return int
     */
    public function time(): int
    {
        return $this->testTime ?? time();
    }
}
