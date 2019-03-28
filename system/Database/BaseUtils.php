<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */

namespace CodeIgniter\Database;

use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Class BaseUtils
 */
abstract class BaseUtils
{

	/**
	 * Database object
	 *
	 * @var object
	 */
	protected $db;

	//--------------------------------------------------------------------

	/**
	 * List databases statement
	 *
	 * @var string
	 */
	protected $listDatabases = false;

	/**
	 * OPTIMIZE TABLE statement
	 *
	 * @var string
	 */
	protected $optimizeTable = false;

	/**
	 * REPAIR TABLE statement
	 *
	 * @var string
	 */
	protected $repairTable = false;

	//--------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @param ConnectionInterface|object $db
	 */
	public function __construct(ConnectionInterface &$db)
	{
		$this->db = & $db;
	}

	//--------------------------------------------------------------------

	/**
	 * List databases
	 *
	 * @return array|boolean
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function listDatabases()
	{
		// Is there a cached result?
		if (isset($this->db->dataCache['db_names']))
		{
			return $this->db->dataCache['db_names'];
		}
		elseif ($this->listDatabases === false)
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('Unsupported feature of the database platform you are using.');
			}
			return false;
		}

		$this->db->dataCache['db_names'] = [];

		$query = $this->db->query($this->listDatabases);
		if ($query === false)
		{
			return $this->db->dataCache['db_names'];
		}

		for ($i = 0, $query = $query->getResultArray(), $c = count($query); $i < $c; $i ++)
		{
			$this->db->dataCache['db_names'][] = current($query[$i]);
		}

		return $this->db->dataCache['db_names'];
	}

	//--------------------------------------------------------------------

	/**
	 * Determine if a particular database exists
	 *
	 * @param  string $database_name
	 * @return boolean
	 */
	public function databaseExists($database_name)
	{
		return in_array($database_name, $this->listDatabases());
	}

	//--------------------------------------------------------------------

	/**
	 * Optimize Table
	 *
	 * @param  string $table_name
	 * @return boolean|mixed
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function optimizeTable($table_name)
	{
		if ($this->optimizeTable === false)
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('Unsupported feature of the database platform you are using.');
			}
			return false;
		}

		$query = $this->db->query(sprintf($this->optimizeTable, $this->db->escapeIdentifiers($table_name)));
		if ($query !== false)
		{
			$query = $query->getResultArray();
			return current($query);
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Optimize Database
	 *
	 * @return mixed
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function optimizeDatabase()
	{
		if ($this->optimizeTable === false)
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('Unsupported feature of the database platform you are using.');
			}
			return false;
		}

		$result = [];
		foreach ($this->db->listTables() as $table_name)
		{
			$res = $this->db->query(sprintf($this->optimizeTable, $this->db->escapeIdentifiers($table_name)));
			if (is_bool($res))
			{
				return $res;
			}

			// Build the result array...
			$res  = $res->getResultArray();
			$res  = current($res);
			$key  = str_replace($this->db->database . '.', '', current($res));
			$keys = array_keys($res);
			unset($res[$keys[0]]);

			$result[$key] = $res;
		}

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Repair Table
	 *
	 * @param  string $table_name
	 * @return mixed
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function repairTable($table_name)
	{
		if ($this->repairTable === false)
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('Unsupported feature of the database platform you are using.');
			}
			return false;
		}

		$query = $this->db->query(sprintf($this->repairTable, $this->db->escapeIdentifiers($table_name)));
		if (is_bool($query))
		{
			return $query;
		}

		$query = $query->getResultArray();
		return current($query);
	}

	//--------------------------------------------------------------------

	/**
	 * Generate CSV from a query result object
	 *
	 * @param ResultInterface $query     Query result object
	 * @param string          $delim     Delimiter (default: ,)
	 * @param string          $newline   Newline character (default: \n)
	 * @param string          $enclosure Enclosure (default: ")
	 *
	 * @return string
	 */
	public function getCSVFromResult(ResultInterface $query, $delim = ',', $newline = "\n", $enclosure = '"')
	{
		$out = '';
		// First generate the headings from the table column names
		foreach ($query->getFieldNames() as $name)
		{
			$out .= $enclosure . str_replace($enclosure, $enclosure . $enclosure, $name) . $enclosure . $delim;
		}

		$out = substr($out, 0, -strlen($delim)) . $newline;

		// Next blast through the result array and build out the rows
		while ($row = $query->getUnbufferedRow('array'))
		{
			$line = [];
			foreach ($row as $item)
			{
				$line[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $item) . $enclosure;
			}
			$out .= implode($delim, $line) . $newline;
		}

		return $out;
	}

	//--------------------------------------------------------------------

	/**
	 * Generate XML data from a query result object
	 *
	 * @param ResultInterface $query  Query result object
	 * @param array           $params Any preferences
	 *
	 * @return string
	 */
	public function getXMLFromResult(ResultInterface $query, $params = [])
	{
		// Set our default values
		foreach (['root' => 'root', 'element' => 'element', 'newline' => "\n", 'tab' => "\t"] as $key => $val)
		{
			if (! isset($params[$key]))
			{
				$params[$key] = $val;
			}
		}

		// Create variables for convenience
		extract($params);

		// Load the xml helper
			  helper('xml');
		// Generate the result
		$xml = '<' . $root . '>' . $newline;
		while ($row = $query->getUnbufferedRow())
		{
			$xml .= $tab . '<' . $element . '>' . $newline;
			foreach ($row as $key => $val)
			{
				$xml .= $tab . $tab . '<' . $key . '>' . xml_convert($val) . '</' . $key . '>' . $newline;
			}
			$xml .= $tab . '</' . $element . '>' . $newline;
		}

		return $xml . '</' . $root . '>' . $newline;
	}

	//--------------------------------------------------------------------

	/**
	 * Database Backup
	 *
	 * @param  array $params
	 * @return mixed
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function backup($params = [])
	{
		// If the parameters have not been submitted as an
		// array then we know that it is simply the table
		// name, which is a valid short cut.
		if (is_string($params))
		{
			$params = ['tables' => $params];
		}

		// Set up our default preferences
		$prefs = [
			'tables'             => [],
			'ignore'             => [],
			'filename'           => '',
			'format'             => 'gzip', // gzip, zip, txt
			'add_drop'           => true,
			'add_insert'         => true,
			'newline'            => "\n",
			'foreign_key_checks' => true,
		];

		// Did the user submit any preferences? If so set them....
		if (! empty($params))
		{
			foreach ($prefs as $key => $val)
			{
				if (isset($params[$key]))
				{
					$prefs[$key] = $params[$key];
				}
			}
		}

		// Are we backing up a complete database or individual tables?
		// If no table names were submitted we'll fetch the entire table list
		if (empty($prefs['tables']))
		{
			$prefs['tables'] = $this->db->listTables();
		}

		// Validate the format
		if (! in_array($prefs['format'], ['gzip', 'zip', 'txt'], true))
		{
			$prefs['format'] = 'txt';
		}

		// Is the encoder supported? If not, we'll either issue an
		// error or use plain text depending on the debug settings
		if (($prefs['format'] === 'gzip' && ! function_exists('gzencode'))
				|| ( $prefs['format'] === 'zip' && ! function_exists('gzcompress')))
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('The file compression format you chose is not supported by your server.');
			}

			$prefs['format'] = 'txt';
		}

		// Was a Zip file requested?
		if ($prefs['format'] === 'zip')
		{
			// Set the filename if not provided (only needed with Zip files)
			if ($prefs['filename'] === '')
			{
				$prefs['filename'] = (count($prefs['tables']) === 1 ? $prefs['tables'] : $this->db->database)
						. date('Y-m-d_H-i', time()) . '.sql';
			}
			else
			{
				// If they included the .zip file extension we'll remove it
				if (preg_match('|.+?\.zip$|', $prefs['filename']))
				{
					$prefs['filename'] = str_replace('.zip', '', $prefs['filename']);
				}

				// Tack on the ".sql" file extension if needed
				if (! preg_match('|.+?\.sql$|', $prefs['filename']))
				{
					$prefs['filename'] .= '.sql';
				}
			}

			// Load the Zip class and output it
			//          $CI =& get_instance();
			//          $CI->load->library('zip');
			//          $CI->zip->add_data($prefs['filename'], $this->_backup($prefs));
			//          return $CI->zip->get_zip();
		}
		elseif ($prefs['format'] === 'txt') // Was a text file requested?
		{
			return $this->_backup($prefs);
		}
		elseif ($prefs['format'] === 'gzip') // Was a Gzip file requested?
		{
			return gzencode($this->_backup($prefs));
		}

		return;
	}

	//--------------------------------------------------------------------

	/**
	 * Platform dependent version of the backup function.
	 *
	 * @param array|null $prefs
	 *
	 * @return mixed
	 */
	abstract public function _backup(array $prefs = null);

	//--------------------------------------------------------------------
}
