<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Entity\Cast;

/**
 * Class BaseCast
 */
abstract class BaseCast implements CastInterface
{
	/**
	 * Get
	 *
	 * @param mixed $value  Data
	 * @param array $params Additional param
	 *
	 * @return mixed
	 */
	public static function get($value, array $params = [])
	{
		return $value;
	}

	/**
	 * Set
	 *
	 * @param mixed $value  Data
	 * @param array $params Additional param
	 *
	 * @return mixed
	 */
	public static function set($value, array $params = [])
	{
		return $value;
	}
}
