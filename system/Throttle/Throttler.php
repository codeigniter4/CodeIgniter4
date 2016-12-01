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
     * @param int    $cost     The number of tokens this action uses.
     *
     * @return bool
     * @internal param int $maxRequests
     */
    public function check(string $key, int $capacity, int $seconds, int $cost = 1)
    {
        $tokenName = $this->prefix.$key;

        // Check to see if the bucket has even been created yet.
        if (($tokens = $this->cache->get($tokenName)) === false)
        {
            // If it hasn't been created, then we'll set it to the maximum
            // capacity - 1, and save it to the cache.
            $this->cache->save($tokenName, $capacity-$cost, $seconds);
            $this->cache->save($tokenName.'Time', time());

            return true;
        }

        // If $tokens > 0, then we need to replenish the bucket
        // based on how long it's been since the last update.
        $throttleTime = $this->cache->get($tokenName.'Time');
        $elapsed      = time() - $throttleTime;
        $rate         = (int)ceil($elapsed / $capacity);

        // We must have a minimum wait of 1 second for a new token.
        $this->tokenTime = max(1, $rate);

        // Add tokens based up on number per second that
        // should be refilled, then checked against capacity
        // to be sure the bucket didn't overflow.
        $tokens += ($rate * $seconds);
        $tokens = $tokens > $capacity
            ? $capacity
            : $tokens;

        // If $tokens > 0, then we are save to perform the action, but
        // we need to decrement the number of available tokens.
        $response = false;

        if ($tokens > 0)
        {
            $response = true;

            $this->cache->save($tokenName, $tokens-$cost, $elapsed);
            $this->cache->save($tokenName.'Time', time());
        }

        return $response;
    }

    //--------------------------------------------------------------------
}
