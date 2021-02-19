<?php


namespace CodeIgniter\EntityCast;

/**
 * Class CastAsInteger
 *
 * @package CodeIgniter\Entity\Cast
 */

class CastAsInteger extends AbstractCast
{

	/**
	 * @inheritDoc
	 */
	public static function get($value, array $params = []) : int
	{
		return (int) $value;
	}
}
