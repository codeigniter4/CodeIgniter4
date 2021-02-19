<?php


namespace CodeIgniter\EntityCast;

/**
 * Class CastAsBoolean
 *
 * @package CodeIgniter\Entity\Cast
 */

class CastAsBoolean extends AbstractCast
{

	/**
	 * @inheritDoc
	 */
	public static function get($value, array $params = []) : bool
	{
		return (bool) $value;
	}
}
