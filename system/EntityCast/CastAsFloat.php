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

/**
 * Class CastAsFloat
 */
class CastAsFloat extends AbstractCast
{

	/**
	 * @inheritDoc
	 */
	public static function get($value, array $params = []): float
	{
		return (float) $value;
	}
}
