<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Session;

/**
 * Expected behavior of a session container used with CodeIgniter.
 */
interface SessionInterface
{
    /**
     * Regenerates the session ID.
     *
     * @param bool $destroy Should old session data be destroyed?
     */
    public function regenerate(bool $destroy = false);

    /**
     * Destroys the current session.
     */
    public function destroy();

    /**
     * Sets user data into the session.
     *
     * If $data is a string, then it is interpreted as a session property
     * key, and  $value is expected to be non-null.
     *
     * If $data is an array, it is expected to be an array of key/value pairs
     * to be set as session properties.
     *
     * @param array|string $data  Property name or associative array of properties
     * @param mixed        $value Property value if single key provided
     */
    public function set($data, $value = null);

    /**
     * Get user data that has been set in the session.
     *
     * If the property exists as "normal", returns it.
     * Otherwise, returns an array of any temp or flash data values with the
     * property key.
     *
     * Replaces the legacy method $session->userdata();
     *
     * @param string $key Identifier of the session property to retrieve
     *
     * @return mixed The property value(s)
     */
    public function get(?string $key = null);

    /**
     * Returns whether an index exists in the session array.
     *
     * @param string $key Identifier of the session property we are interested in.
     */
    public function has(string $key): bool;

    /**
     * Remove one or more session properties.
     *
     * If $key is an array, it is interpreted as an array of string property
     * identifiers to remove. Otherwise, it is interpreted as the identifier
     * of a specific session property to remove.
     *
     * @param array|string $key Identifier of the session property or properties to remove.
     */
    public function remove($key);

    /**
     * Sets data into the session that will only last for a single request.
     * Perfect for use with single-use status update messages.
     *
     * If $data is an array, it is interpreted as an associative array of
     * key/value pairs for flashdata properties.
     * Otherwise, it is interpreted as the identifier of a specific
     * flashdata property, with $value containing the property value.
     *
     * @param array|string $data  Property identifier or associative array of properties
     * @param array|string $value Property value if $data is a scalar
     */
    public function setFlashdata($data, $value = null);

    /**
     * Retrieve one or more items of flash data from the session.
     *
     * If the item key is null, return all flashdata.
     *
     * @param string $key Property identifier
     *
     * @return array|null The requested property value, or an associative
     *                    array  of them
     */
    public function getFlashdata(?string $key = null);

    /**
     * Keeps a single piece of flash data alive for one more request.
     *
     * @param array|string $key Property identifier or array of them
     */
    public function keepFlashdata($key);

    /**
     * Mark a session property or properties as flashdata.
     *
     * @param array|string $key Property identifier or array of them
     *
     * @return false if any of the properties are not already set
     */
    public function markAsFlashdata($key);

    /**
     * Unmark data in the session as flashdata.
     *
     * @param array|string $key Property identifier or array of them
     */
    public function unmarkFlashdata($key);

    /**
     * Retrieve all of the keys for session data marked as flashdata.
     *
     * @return array The property names of all flashdata
     */
    public function getFlashKeys(): array;

    /**
     * Sets new data into the session, and marks it as temporary data
     * with a set lifespan.
     *
     * @param array|string $data  Session data key or associative array of items
     * @param mixed        $value Value to store
     * @param int          $ttl   Time-to-live in seconds
     */
    public function setTempdata($data, $value = null, int $ttl = 300);

    /**
     * Returns either a single piece of tempdata, or all temp data currently
     * in the session.
     *
     * @param string $key Session data key
     *
     * @return mixed Session data value or null if not found.
     */
    public function getTempdata(?string $key = null);

    /**
     * Removes a single piece of temporary data from the session.
     *
     * @param string $key Session data key
     */
    public function removeTempdata(string $key);

    /**
     * Mark one of more pieces of data as being temporary, meaning that
     * it has a set lifespan within the session.
     *
     * @param array|string $key Property identifier or array of them
     * @param int          $ttl Time to live, in seconds
     *
     * @return bool False if any of the properties were not set
     */
    public function markAsTempdata($key, int $ttl = 300);

    /**
     * Unmarks temporary data in the session, effectively removing its
     * lifespan and allowing it to live as long as the session does.
     *
     * @param array|string $key Property identifier or array of them
     */
    public function unmarkTempdata($key);

    /**
     * Retrieve the keys of all session data that have been marked as temporary data.
     */
    public function getTempKeys(): array;
}
