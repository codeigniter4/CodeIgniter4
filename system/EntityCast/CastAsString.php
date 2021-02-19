<?php


namespace CodeIgniter\EntityCast;

/**
 * Class CastAsString
 *
 * @package CodeIgniter\Entity\Cast
 */

class CastAsString extends AbstractCast
{

	/**
	 * @inheritDoc
	 */
	public static function get($value, array $params = []) : string
	{
		return (string) $value;
	}
}
