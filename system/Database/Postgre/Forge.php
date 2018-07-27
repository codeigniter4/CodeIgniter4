<?php namespace CodeIgniter\Database\Postgre;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * Forge for Postgre
 */
class Forge extends \CodeIgniter\Database\Forge
{

	/**
	 * DROP CONSTRAINT statement
	 *
	 * @var    string
	 */
	protected $dropConstraintStr = 'ALTER TABLE %s DROP CONSTRAINT %s';


	/**
	 * UNSIGNED support
	 *
	 * @var    array
	 */
	protected $_unsigned = [
		'INT2'		 => 'INTEGER',
		'SMALLINT'	 => 'INTEGER',
		'INT'		 => 'BIGINT',
		'INT4'		 => 'BIGINT',
		'INTEGER'	 => 'BIGINT',
		'INT8'		 => 'NUMERIC',
		'BIGINT'	 => 'NUMERIC',
		'REAL'		 => 'DOUBLE PRECISION',
		'FLOAT'		 => 'DOUBLE PRECISION'
	];

	/**
	 * NULL value representation in CREATE/ALTER TABLE statements
	 *
	 * @var    string
	 */
	protected $_null = 'NULL';

	//--------------------------------------------------------------------

	/**
	 * CREATE TABLE attributes
	 *
	 * @param	array	$attributes	Associative array of table attributes
	 * @return	string
	 */
	protected function _createTableAttributes($attributes)
	{
		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * ALTER TABLE
	 *
	 * @param    string $alter_type ALTER type
	 * @param    string $table      Table name
	 * @param    mixed  $field      Column definition
	 *
	 * @return    string|array
	 */
	protected function _alterTable($alter_type, $table, $field)
	{
		if (in_array($alter_type, ['DROP', 'ADD'], true))
		{
			return parent::_alterTable($alter_type, $table, $field);
		}

		$sql = 'ALTER TABLE ' . $this->db->escapeIdentifiers($table);
		$sqls = [];
		for ($i = 0, $c = count($field); $i < $c; $i ++ )
		{
			if ($field[$i]['_literal'] !== false)
			{
				return false;
			}

			if (version_compare($this->db->getVersion(), '8', '>=') && isset($field[$i]['type']))
			{
				$sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($field[$i]['name'])
						. " TYPE {$field[$i]['type']}{$field[$i]['length']}";
			}

			if ( ! empty($field[$i]['default']))
			{
				$sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($field[$i]['name'])
						. " SET DEFAULT {$field[$i]['default']}";
			}

			if (isset($field[$i]['null']))
			{
				$sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($field[$i]['name'])
						. ($field[$i]['null'] === true ? ' DROP' : ' SET') . ' NOT NULL';
			}

			if ( ! empty($field[$i]['new_name']))
			{
				$sqls[] = $sql . ' RENAME COLUMN ' . $this->db->escapeIdentifiers($field[$i]['name'])
						. ' TO ' . $this->db->escapeIdentifiers($field[$i]['new_name']);
			}

			if ( ! empty($field[$i]['comment']))
			{
				$sqls[] = 'COMMENT ON COLUMN' . $this->db->escapeIdentifiers($table)
						. '.' . $this->db->escapeIdentifiers($field[$i]['name'])
						. " IS {$field[$i]['comment']}";
			}
		}

		return $sqls;
	}

        //--------------------------------------------------------------------

	/**
	 * Process column
	 *
	 * @param	array	$field
	 * @return	string
	 */
	protected function _processColumn($field)
	{
		return $this->db->escapeIdentifiers($field['name'])
				. ' ' . $field['type'] . $field['length']
				. $field['default']
				. $field['null']
				. $field['auto_increment']
				. $field['unique'];
	}


	//--------------------------------------------------------------------

	/**
	 * Field attribute TYPE
	 *
	 * Performs a data type mapping between different databases.
	 *
	 * @param    array &$attributes
	 *
	 * @return    void
	 */
	protected function _attributeType(&$attributes)
	{
		// Reset field lengths for data types that don't support it
		if (isset($attributes['CONSTRAINT']) && stripos($attributes['TYPE'], 'int') !== false)
		{
			$attributes['CONSTRAINT'] = null;
		}

		switch (strtoupper($attributes['TYPE']))
		{
			case 'TINYINT':
				$attributes['TYPE'] = 'SMALLINT';
				$attributes['UNSIGNED'] = false;
				return;
			case 'MEDIUMINT':
				$attributes['TYPE'] = 'INTEGER';
				$attributes['UNSIGNED'] = false;
				return;
			case 'DATETIME':
				$attributes['TYPE'] = 'TIMESTAMP';
			default:
				return;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute AUTO_INCREMENT
	 *
	 * @param    array &$attributes
	 * @param    array &$field
	 *
	 * @return    void
	 */
	protected function _attributeAutoIncrement(&$attributes, &$field)
	{
		if ( ! empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === true)
		{
			$field['type'] = $field['type'] === 'NUMERIC' ? 'BIGSERIAL' : 'SERIAL';
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Drop Table
	 *
	 * Generates a platform-specific DROP TABLE string
	 *
	 * @param    string $table     Table name
	 * @param    bool   $if_exists Whether to add an IF EXISTS condition
	 * @param bool      $cascade
	 *
	 * @return    string
	 */
	protected function _dropTable($table, $if_exists, $cascade)
	{
		$sql = parent::_dropTable($table, $if_exists, $cascade);

		if ($cascade === true)
		{
			$sql .= ' CASCADE';
		}

		return $sql;
	}

	//--------------------------------------------------------------------

}
