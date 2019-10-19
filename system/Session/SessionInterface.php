<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
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
	 * @param boolean $destroy Should old session data be destroyed?
	 */
	public function regenerate(bool $destroy = false);

	//--------------------------------------------------------------------

	/**
	 * Destroys the current session.
	 */
	public function destroy();

	//--------------------------------------------------------------------

	/**
	 * Sets user data into the session.
	 *
	 * If $data is a string, then it is interpreted as a session property
	 * key, and  $value is expected to be non-null.
	 *
	 * If $data is an array, it is expected to be an array of key/value pairs
	 * to be set as session properties.
	 *
	 * @param string|array $data  Property name or associative array of properties
	 * @param string|array $value Property value if single key provided
	 */
	public function set($data, $value = null);

	//--------------------------------------------------------------------

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
	 * @return array|null    The property value(s)
	 */
	public function get(string $key = null);

	//--------------------------------------------------------------------

	/**
	 * Returns whether an index exists in the session array.
	 *
	 * @param string $key Identifier of the session property we are interested in.
	 *
	 * @return boolean
	 */
	public function has(string $key): bool;

	//--------------------------------------------------------------------

	/**
	 * Remove one or more session properties.
	 *
	 * If $key is an array, it is interpreted as an array of string property
	 * identifiers to remove. Otherwise, it is interpreted as the identifier
	 * of a specific session property to remove.
	 *
	 * @param string|array $key Identifier of the session property or properties to remove.
	 */
	public function remove($key);

	//--------------------------------------------------------------------

	/**
	 * Sets data into the session that will only last for a single request.
	 * Perfect for use with single-use status update messages.
	 *
	 * If $data is an array, it is interpreted as an associative array of
	 * key/value pairs for flashdata properties.
	 * Otherwise, it is interpreted as the identifier of a specific
	 * flashdata property, with $value containing the property value.
	 *
	 * @param string|array $data  Property identifier or associative array of properties
	 * @param string|array $value Property value if $data is a scalar
	 */
	public function setFlashdata($data, $value = null);

	//--------------------------------------------------------------------

	/**
	 * Retrieve one or more items of flash data from the session.
	 *
	 * If the item key is null, return all flashdata.
	 *
	 * @param  string $key Property identifier
	 * @return array|null	The requested property value, or an associative
	 *     array  of them
	 */
	public function getFlashdata(string $key = null);

	//--------------------------------------------------------------------

	/**
	 * Keeps a single piece of flash data alive for one more request.
	 *
	 * @param array|string $key Property identifier or array of them
	 */
	public function keepFlashdata($key);

	//--------------------------------------------------------------------

	/**
	 * Mark a session property or properties as flashdata.
	 *
	 * @param string|array $key Property identifier or array of them
	 *
	 * @return False if any of the properties are not already set
	 */
	public function markAsFlashdata($key);

	//--------------------------------------------------------------------

	/**
	 * Unmark data in the session as flashdata.
	 *
	 * @param string|array $key	Property identifier or array of them
	 */
	public function unmarkFlashdata($key);

	//--------------------------------------------------------------------

	/**
	 * Retrieve all of the keys for session data marked as flashdata.
	 *
	 * @return array	The property names of all flashdata
	 */
	public function getFlashKeys(): array;

	//--------------------------------------------------------------------

	/**
	 * Sets new data into the session, and marks it as temporary data
	 * with a set lifespan.
	 *
	 * @param string|array $data  Session data key or associative array of items
	 * @param mixed        $value Value to store
	 * @param integer      $ttl   Time-to-live in seconds
	 */
	public function setTempdata($data, $value = null, int $ttl = 300);

	//--------------------------------------------------------------------

	/**
	 * Returns either a single piece of tempdata, or all temp data currently
	 * in the session.
	 *
	 * @param  string $key Session data key
	 * @return mixed        Session data value or null if not found.
	 */
	public function getTempdata(string $key = null);

	//--------------------------------------------------------------------

	/**
	 * Removes a single piece of temporary data from the session.
	 *
	 * @param string $key Session data key
	 */
	public function removeTempdata(string $key);

	//--------------------------------------------------------------------

	/**
	 * Mark one of more pieces of data as being temporary, meaning that
	 * it has a set lifespan within the session.
	 *
	 * @param string|array $key Property identifier or array of them
	 * @param integer      $ttl Time to live, in seconds
	 *
	 * @return boolean    False if any of the properties were not set
	 */
	public function markAsTempdata($key, int $ttl = 300);

	//--------------------------------------------------------------------

	/**
	 * Unmarks temporary data in the session, effectively removing its
	 * lifespan and allowing it to live as long as the session does.
	 *
	 * @param string|array $key	Property identifier or array of them
	 */
	public function unmarkTempdata($key);

	//--------------------------------------------------------------------

	/**
	 * Retrieve the keys of all session data that have been marked as temporary data.
	 *
	 * @return array
	 */
	public function getTempKeys(): array;

	//--------------------------------------------------------------------
}
