<?php


namespace CodeIgniter\EntityCast;

use CodeIgniter\Exceptions\CastException;

/**
 * Class CastAsJson
 *
 * @package CodeIgniter\Entity\Cast
 */
class CastAsJson extends AbstractCast
{

	/**
	 * @inheritDoc
	 */
	public static function get($value, array $params = [])
	{
		$tmp = ! is_null($value) ? (in_array('array', $params, true) ? [] : new \stdClass) : null;
		if (function_exists('json_decode')
			&& ((is_string($value)
					&& strlen($value) > 1
					&& in_array($value[0], ['[', '{', '"'], true))
				|| is_numeric($value)
			)
		)
		{
			$tmp = json_decode($value, (bool) $params);
			if (json_last_error() !== JSON_ERROR_NONE)
			{
				throw CastException::forInvalidJsonFormatException(json_last_error());
			}
		}

		return $tmp;
	}

	/**
	 * @inheritDoc
	 */
	public static function set($value, array $params = []) : string
	{
		if (function_exists('json_encode'))
		{
			$value = json_encode($value, JSON_UNESCAPED_UNICODE);

			if (json_last_error() !== JSON_ERROR_NONE)
			{
				throw CastException::forInvalidJsonFormatException(json_last_error());
			}
		}

		return $value;
	}
}
