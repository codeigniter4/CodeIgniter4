<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Class SQLStatement
 *
 * Wrapper for a raw SQL subquery
 */
class SqlExpression
{
	/**
	 * SQL Statement
	 *
	 * @var string
	 */
	protected $value;

	/**
	 * SQLStatement constructor.
	 *
	 * @param string $value SQL Statement
	 */
	public function __construct(string $value)
	{
		if (empty($value))
		{
			throw new DatabaseException('SQL expression cannot be empty.');
		}

		$this->value = $value;
	}

	/**
	 * Return SQL Statement
	 *
	 * @return string
	 */
	public function getValue(): string
	{
		return $this->value;
	}

	/**
	 * Return SQL Statement
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getValue();
	}
}
