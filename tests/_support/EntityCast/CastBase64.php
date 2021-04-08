<?php


namespace Tests\Support\EntityCast;

class CastBase64 extends \CodeIgniter\EntityCast\AbstractCast
{
	/**
	 * Get
	 *
	 * @param mixed $value  Data
	 * @param array $params Additional param
	 *
	 * @return mixed
	 */
	public static function get($value, array $params = []) : string
	{
		return base64_decode($value);
	}

	/**
	 * Set
	 *
	 * @param mixed $value  Data
	 * @param array $params Additional param
	 *
	 * @return mixed
	 */
	public static function set($value, array $params = []) : string
	{
		return base64_encode($value);
	}
}
