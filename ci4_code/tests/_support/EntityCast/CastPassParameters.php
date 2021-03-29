<?php


namespace Tests\Support\EntityCast;

class CastPassParameters extends \CodeIgniter\EntityCast\AbstractCast
{
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
		return $value . ':' . json_encode($params);
	}
}
