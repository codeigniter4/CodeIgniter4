<?php namespace CodeIgniter\Session;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

interface SessionInterface
{
	public function initialize();

	//--------------------------------------------------------------------

	/**
	 * Regenerates the session ID.
	 *
	 * @param bool $destroy Should old session data be destroyed?
	 */
	public function regenerate($destroy = false);

	//--------------------------------------------------------------------

	/**
	 * Destroys the current session.
	 */
	public function destroy();

	//--------------------------------------------------------------------

	/**
	 * Sets user data into the session.
	 *
	 * @param      $data
	 * @param null $value
	 */
	public function set($data, $value = null);

	//--------------------------------------------------------------------

	/**
	 * Get any user data that has been set in the session.
	 *
	 * Replaces the legacy method $session->userdata();
	 *
	 * @param null $key
	 *
	 * @return array|null
	 */
	public function get($key = null);

	//--------------------------------------------------------------------

	/**
	 * Returns whether an index exists in the session array.
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function has($key);

	//--------------------------------------------------------------------

	/**
	 * Unsets one or more bits of session data.
	 *
	 * @param $key
	 */
	public function unset($key);

	//--------------------------------------------------------------------

	/**
	 * Sets data into the session that will only last for a single request.
	 * Perfect for use with single-use status update messages.
	 *
	 * @param      $data
	 * @param null $value
	 */
	public function setFlashdata($data, $value = null);

	//--------------------------------------------------------------------

	/**
	 * Grabs one or more items of flash data from the session.
	 *
	 * @param null $key
	 *
	 * @return array|null
	 */
	public function getFlashdata($key = null);

	//--------------------------------------------------------------------

	/**
	 * Keeps a single piece of flash data alive for one more request.
	 *
	 * @param $key
	 *
	 * @return $this
	 */
	public function keepFlashdata($key);

	//--------------------------------------------------------------------

	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public function markAsFlashdata($key);

	//--------------------------------------------------------------------

	/**
	 * Unmark data in the session as flashdata.
	 *
	 * @param mixed $key
	 */
	public function unmarkFlashdata($key);

	//--------------------------------------------------------------------

	/**
	 * Grabs all of the keys for session data marked as flashdata.
	 *
	 * @return array
	 */
	public function getFlashKeys();

	//--------------------------------------------------------------------

	/**
	 * Sets new data into the session, and marks it as temporary data
	 * with a set lifespan.
	 *
	 * @param      $data    Session data key or associative array of items
	 * @param null $value   Value to store
	 * @param int  $ttl     Time-to-live in seconds
	 */
	public function setTempdata($data, $value = null, $ttl = 300);

	//--------------------------------------------------------------------

	/**
	 * Returns either a single piece of tempdata, or all temp data currently in the session.
	 *
	 * @param string $key   Session data key
	 *
	 * @return mixed        Session data value or null if not found.
	 */
	public function getTempdata($key = null);

	//--------------------------------------------------------------------

	/**
	 * Removes a single piece of temporary data from the session.
	 *
	 * @param $key
	 */
	public function unsetTempdata($key);

	//--------------------------------------------------------------------

	/**
	 * Mark one of more pieces of data as being temporary, meaning that
	 * it has a set lifespan within the session.
	 *
	 * @param     $key
	 * @param int $ttl
	 *
	 * @return bool
	 */
	public function markAsTempdata($key, $ttl = 300);

	//--------------------------------------------------------------------

	/**
	 * Unmarks temporary data in the session, effectively removing its
	 * lifespan and allowing it to live as long as the session does.
	 *
	 * @param $key
	 */
	public function unmarkTempdata($key);

	//--------------------------------------------------------------------

	/**
	 * Grabs the keys of all session data that has been marked as temporary data.
	 *
	 * @return array
	 */
	public function getTempKeys();

	//--------------------------------------------------------------------

}
