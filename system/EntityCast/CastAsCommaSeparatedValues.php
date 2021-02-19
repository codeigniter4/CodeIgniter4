<?php


namespace CodeIgniter\EntityCast;

/**
 * Class CastAsCommaSeparatedValues
 *
 * @package CodeIgniter\Entity\Cast
 */

class CastAsCommaSeparatedValues extends AbstractCast
{

	/**
	 * @inheritDoc
	 */
	public static function get($value, array $params = []) : array
	{
		return explode(',', $value);
	}

	/**
	 * @inheritDoc
	 */
	public static function set($value, array $params = []) : string
	{
		return implode(',', $value);
	}
}
