<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\EntityCast;

use JsonException;
use CodeIgniter\Exceptions\CastException;
use stdClass;

/**
 * Class CastAsJson
 */
class CastAsJson extends AbstractCast
{

	/**
	 * @inheritDoc
	 */
	public static function get($value, array $params = [])
	{
		$associative = in_array('array', $params, true);
		$tmp         = ! is_null($value) ? ($associative ? [] : new stdClass) : null;

		if (function_exists('json_decode')
			&& ((is_string($value)
					&& strlen($value) > 1
					&& in_array($value[0], ['[', '{', '"'], true))
				|| is_numeric($value)
			)
		)
		{
			try
			{
				$tmp = json_decode($value, $associative, 512, JSON_THROW_ON_ERROR);
			}
			catch (JsonException $e)
			{
				throw CastException::forInvalidJsonFormatException($e->getCode());
			}
		}

		return $tmp;
	}

	/**
	 * @inheritDoc
	 */
	public static function set($value, array $params = []): string
	{
		if (function_exists('json_encode'))
		{
			try
			{
				$value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
			}
			catch (JsonException $e)
			{
				throw CastException::forInvalidJsonFormatException($e->getCode());
			}
		}

		return $value;
	}
}
