<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Entity\Cast;

use CodeIgniter\Entity\Exceptions\CastException;

/**
 * Class TimestampCast
 */
class TimestampCast extends BaseCast
{
	/**
	 * @inheritDoc
	 */
	public static function get($value, array $params = [])
	{
		$value = strtotime($value);

		if ($value === false)
		{
			throw CastException::forInvalidTimestamp();
		}

		return $value;
	}
}
