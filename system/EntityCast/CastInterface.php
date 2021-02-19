<?php


namespace CodeIgniter\EntityCast;

interface CastInterface
{
	/**
	 * Get
	 *
	 * @param mixed $value  Data
	 * @param array $params Additional param
	 *
	 * @return mixed
	 */
	public static function get($value, array $params = []);

	/**
	 * Set
	 *
	 * @param mixed $value  Data
	 * @param array $params Additional param
	 *
	 * @return mixed
	 */
	public static function set($value, array $params = []);
}
