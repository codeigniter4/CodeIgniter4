<?php


namespace CodeIgniter\EntityCast;

/**
 * Class CastAsObject
 *
 * @package CodeIgniter\Entity\Cast
 */

class CastAsObject extends AbstractCast
{

	/**
	 * @inheritDoc
	 */
	public static function get($value, array $params = []) : object
	{
		return (object) $value;
	}
}
