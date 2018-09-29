<?php namespace CodeIgniter\Cache;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
interface CacheInterface
{

	/**
	 * Takes care of any handler-specific setup that must be done.
	 */
	public function initialize();

	//--------------------------------------------------------------------

	/**
	 * Attempts to fetch an item from the cache store.
	 *
	 * @param string $key  Cache item name
	 *
	 * @return mixed
	 */
	public function get(string $key);

	//--------------------------------------------------------------------

	/**
	 * Saves an item to the cache store.
	 *
	 * @param string $key   Cache item name
	 * @param mixed  $value The data to save
	 * @param int    $ttl   Time To Live, in seconds (default 60)
	 *
	 * @return mixed
	 */
	public function save(string $key, $value, int $ttl = 60);

	//--------------------------------------------------------------------

	/**
	 * Deletes a specific item from the cache store.
	 *
	 * @param string $key  Cache item name
	 *
	 * @return mixed
	 */
	public function delete(string $key);

	//--------------------------------------------------------------------

	/**
	 * Performs atomic incrementation of a raw stored value.
	 *
	 * @param string $key    Cache ID
	 * @param int    $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function increment(string $key, int $offset = 1);

	//--------------------------------------------------------------------

	/**
	 * Performs atomic decrementation of a raw stored value.
	 *
	 * @param string $key    Cache ID
	 * @param int    $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function decrement(string $key, int $offset = 1);

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return mixed
	 */
	public function clean();

	//--------------------------------------------------------------------

	/**
	 * Returns information on the entire cache.
	 *
	 * The information returned and the structure of the data
	 * varies depending on the handler.
	 *
	 * @return mixed
	 */
	public function getCacheInfo();

	//--------------------------------------------------------------------

	/**
	 * Returns detailed information about the specific item in the cache.
	 *
	 * @param string $key  Cache item name.
	 *
	 * @return mixed
	 */
	public function getMetaData(string $key);

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 *
	 * @return boolean
	 */
	public function isSupported(): bool;

	//--------------------------------------------------------------------
}
