<?php namespace CodeIgniter\Throttle;

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
     * @param string $ip
     * @param int    $maxRequests
     * @param int    $seconds
     *
     * @return bool
     */
    public function check(string $key, int $maxRequests, int $seconds);

    //--------------------------------------------------------------------

}
