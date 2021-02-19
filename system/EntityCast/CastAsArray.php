<?php


namespace CodeIgniter\EntityCast;

/**
 * Class CastAsArray
 *
 * @package CodeIgniter\Entity\Cast
 */

class CastAsArray extends AbstractCast
{

	/**
	 * @inheritDoc
	 */
	public static function get($value, array $params = []) : array
	{
		if (is_string($value) && (strpos($value, 'a:') === 0 || strpos($value, 's:') === 0))
		{
			$value = unserialize($value);
		}

		return (array) $value;
	}

	/**
	 * @inheritDoc
	 */
	public static function set($value, array $params = []) : string
	{
		return serialize($value);
	}
}
