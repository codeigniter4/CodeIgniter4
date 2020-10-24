<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

/**
 * Class Config
 *
 * @deprecated Use CodeIgniter\Config\Factories::config()
 */
class Config
{
	/**
	 * Create new configuration instances or return
	 * a shared instance
	 *
	 * @param string  $name      Configuration name
	 * @param boolean $getShared Use shared instance
	 *
	 * @return mixed|null
	 */
	public static function get(string $name, bool $getShared = true)
	{
		return Factories::config($name, ['getShared' => $getShared]);
	}

	/**
	 * Helper method for injecting mock instances while testing.
	 *
	 * @param string $name
	 * @param object $instance
	 */
	public static function injectMock(string $name, $instance)
	{
		Factories::injectMock('config', $name, $instance);
	}

	/**
	 * Resets the static arrays
	 */
	public static function reset()
	{
		Factories::reset('config');
	}
}
