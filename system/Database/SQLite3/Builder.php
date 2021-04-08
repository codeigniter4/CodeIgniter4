<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\SQLite3;

use CodeIgniter\Database\BaseBuilder;

/**
 * Builder for SQLite3
 */
class Builder extends BaseBuilder
{
	/**
	 * Default installs of SQLite typically do not
	 * support limiting delete clauses.
	 *
	 * @var boolean
	 */
	protected $canLimitDeletes = false;

	/**
	 * Default installs of SQLite do no support
	 * limiting update queries in combo with WHERE.
	 *
	 * @var boolean
	 */
	protected $canLimitWhereUpdates = false;

	/**
	 * ORDER BY random keyword
	 *
	 * @var array
	 */
	protected $randomKeyword = [
		'RANDOM()',
	];

	/**
	 * @var array
	 */
	protected $supportedIgnoreStatements = [
		'insert' => 'OR IGNORE',
	];

	//--------------------------------------------------------------------

	/**
	 * Replace statement
	 *
	 * Generates a platform-specific replace string from the supplied data
	 *
	 * @param string $table  the table name
	 * @param array  $keys   the insert keys
	 * @param array  $values the insert values
	 *
	 * @return string
	 */
	protected function _replace(string $table, array $keys, array $values): string
	{
		return 'INSERT OR ' . parent::_replace($table, $keys, $values);
	}

	//--------------------------------------------------------------------

	/**
	 * Truncate statement
	 *
	 * Generates a platform-specific truncate string from the supplied data
	 *
	 * If the database does not support the TRUNCATE statement,
	 * then this method maps to 'DELETE FROM table'
	 *
	 * @param  string $table
	 * @return string
	 */
	protected function _truncate(string $table): string
	{
		return 'DELETE FROM ' . $table;
	}
}
