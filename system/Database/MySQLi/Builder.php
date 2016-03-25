<?php namespace CodeIgniter\Database\MySQLi;

use CodeIgniter\Database\BaseBuilder;

class Builder extends BaseBuilder
{
	/**
	 * Identifier escape character
	 *
	 * @var    string
	 */
	protected $escapeChar = '`';
}
