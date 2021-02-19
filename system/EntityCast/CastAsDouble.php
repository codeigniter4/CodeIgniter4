<?php


namespace CodeIgniter\EntityCast;

/**
 * Class CastAsDouble
 *
 * @package CodeIgniter\Entity\Cast
 */

class CastAsDouble extends AbstractCast
{
	/**
	 * @inheritDoc
	 */
	public static function get($value, array $params = [])
	{
		return (double) $value;
	}
}
