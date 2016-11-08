<?php namespace CodeIgniter\Throttle;

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
 *
 * @package CodeIgniter\Throttle
 */
class Throttler implements ThrottlerInterface
{
    /**
     * @var \CodeIgniter\Cache\CacheInterface
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

    //--------------------------------------------------------------------

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
    public function getTokenTime()
    {
        return (int)$this->tokenTime;
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
     *
     * @return bool
     * @internal param int $maxRequests
     */
    public function check(string $key, int $capacity, int $seconds)
    {
        $tokenName = $this->prefix.$key;

        // Check to see if the bucket has even been created yet.
        if (($tokens = $this->cache->get($tokenName)) === false)
        {
            // If it hasn't been created, then we'll set it to the maximum
            // capacity - 1, and save it to the cache.
            $this->cache->save($tokenName, $capacity-1, $seconds);

            return true;
        }

        // If $tokens > 0, then we are save to perform the action, but
        // we need to decrement the number of available tokens.
        if ($tokens > 0)
        {
            $response = true;

            $this->cache->decrement($tokenName);
        }
        else
        {
            $response = false;

            // Save the time until the next token is available
            // in case the caller wants to do something with it.
            $this->tokenTime = (int)round($seconds / $capacity);
        }

        return $response;
    }

    //--------------------------------------------------------------------
}
