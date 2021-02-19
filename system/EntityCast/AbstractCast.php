<?php


namespace CodeIgniter\EntityCast;

/**
 * Class AbstractCast
 *
 * @package CodeIgniter\EntityCast
 */

class AbstractCast implements CastInterface
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
