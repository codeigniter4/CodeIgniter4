<?php


namespace CodeIgniter\EntityCast;

/**
 * Class CastAsFloat
 *
 * @package CodeIgniter\Entity\Cast
 */

class CastAsFloat extends AbstractCast
{

	/**
	 * @inheritDoc
	 */
	public static function get($value, array $params = []) : float
	{
		return (float) $value;
	}
}
