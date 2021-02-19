<?php


namespace CodeIgniter\EntityCast;

/**
 * Class CastAsTimestamp
 *
 * @package CodeIgniter\Entity\Cast
 */

class CastAsTimestamp extends AbstractCast
{

	/**
	 * @inheritDoc
	 */
	public static function get($value, array $params = [])
	{
		return strtotime($value);
	}
}
