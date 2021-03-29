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

use Exception;
use DateTime;
use CodeIgniter\I18n\Time;

/**
 * Class CastAsDatetime
 */
class CastAsDatetime extends AbstractCast
{

	/**
	 * @inheritDoc
	 *
	 * @throws Exception
	 */
	public static function get($value, array $params = [])
	{
		if ($value instanceof Time)
		{
			return $value;
		}

		if ($value instanceof DateTime)
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
