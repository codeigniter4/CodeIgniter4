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

use CodeIgniter\Exceptions\CastException;

/**
 * Class CastAsTimestamp
 */
class CastAsTimestamp extends AbstractCast
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
