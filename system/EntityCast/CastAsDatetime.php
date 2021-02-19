<?php


namespace CodeIgniter\EntityCast;

use CodeIgniter\I18n\Time;

/**
 * Class CastAsDatetime
 *
 * @package CodeIgniter\Entity\Cast
 */
class CastAsDatetime extends AbstractCast
{

	/**
	 * @inheritDoc
	 *
	 * @throws \Exception
	 */
	public static function get($value, array $params = [])
	{
		if ($value instanceof Time)
		{
			return $value;
		}

		if ($value instanceof \DateTime)
		{
			return Time::instance($value);
		}

		if (is_numeric($value))
		{
			return Time::createFromTimestamp($value);
		}

		if (is_string($value))
		{
			return Time::parse($value);
		}

		return $value;
	}
}
