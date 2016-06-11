<?php namespace CodeIgniter\Database\Ibase;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\DatabaseException;

/**
 * Connection for Ibase
 */
class Connection extends BaseConnection implements ConnectionInterface
{
	/**
	 * Database driver
	 *
	 * @var	string
	 */
	public $dbdriver = 'Ibase';

	// --------------------------------------------------------------------

	/**
	 * IBase Transaction status flag
	 *
	 * @var	resource
	 */
	protected $_ibase_trans;
    
    // --------------------------------------------------------------------
    
    /**
	 * Result ID
	 *
	 * @var    object|resource
	 */
	public $resultID = false;
    
	// --------------------------------------------------------------------

	/**
	 * Non-persistent database connection
	 *
	 * @param	bool	$persistent
	 * @return	resource
	 */
	public function connect($persistent = FALSE)
	{
		return ($persistent === TRUE)
			? ibase_pconnect($this->hostname.':'.$this->database, $this->username, $this->password, $this->charset)
			: ibase_connect($this->hostname.':'.$this->database, $this->username, $this->password, $this->charset);
	}

	/**
	 * Non-persistent database connection
	 *
	 * @param	bool	$persistent
	 * @return	resource
	 */
	public function reconnect($persistent = FALSE)
	{

		return ($persistent === TRUE)
			? ibase_pconnect($this->hostname.':'.$this->database, $this->username, $this->password, $this->charset)
			: ibase_connect($this->hostname.':'.$this->database, $this->username, $this->password, $this->charset);
	}

	// --------------------------------------------------------------------

	/**
	 * Database version number
	 *
	 * @return	string
	 */
	public function version()
	{
		if (isset($this->data_cache['version']))
		{
			return $this->data_cache['version'];
		}

		if (($service = ibase_service_attach($this->hostname, $this->username, $this->password)))
		{
			$this->data_cache['version'] = ibase_server_info($service, IBASE_SVC_SERVER_VERSION);

			// Don't keep the service open
			ibase_service_detach($service);
			return $this->data_cache['version'];
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Execute the query prepared statement 
     * Why this?? because ibase blob not survive an escape string
	 *
	 * @param string $sql
	 * @param array  ...$binds
	 * @param $queryClass
	 * @return mixed
	 */
    public function query(string $sql, $binds = null, $queryClass = 'CodeIgniter\\Database\\Query')
	{
        if (empty($this->connID)) 
        {
			$this->initialize();
		}
        
        $resultClass = str_replace('Connection', 'Result', get_class($this));
                
        $query = new $queryClass($this);

		$startTime = microtime(true);
                        
        if (!is_null($binds)) 
        {
            $this->resultID = ibase_prepare(isset($this->_ibase_trans) ? $this->_ibase_trans : $this->connID, $sql);
            
            array_unshift($binds, $this->resultID);
            
            $this->resultID = call_user_func_array('ibase_execute', $binds);
            
        } 
        else 
        {
            $this->resultID = ibase_query(isset($this->_ibase_trans) ? $this->_ibase_trans : $this->connID, $sql);
        }
        
        $query->setDuration($startTime);
        
        if ($this->saveQueries) 
        {
            $this->queries[] = $query;
        }
        
        return new $resultClass($this->connID, $this->resultID);
    }
    
    // --------------------------------------------------------------------

	/**
	 * Execute the query
	 *
	 * @param	string	$sql	an SQL query
	 * @return	resource
	 */
	protected function execute($sql)
	{
		return ibase_query(isset($this->_ibase_trans) ? $this->_ibase_trans : $this->connID, $sql);
	}
    
    // --------------------------------------------------------------------

	/**
	 * Creates a BLOB, reads an entire file into it, closes it and returns the assigned BLOB id 
	 *
	 * @param	string	$sql	an SQL query
	 * @return	resource
	 */
	public function create_blob($file_handle)
	{
        if (empty($this->connID)) 
        {
			$this->initialize();
		}
        
        if(strlen($file_handle) == 0)
            return false;
        
        $this->resultID = ibase_blob_create($this->connID);
        ibase_blob_add($this->resultID, $file_handle);       
        return ibase_blob_close($this->resultID);
	}

	// --------------------------------------------------------------------

	/**
	 * Commit transaction
	 *
	 * @return	void
	 */
	public function commit() {
		ibase_commit($this->connID);
	}


	/**
	 * Select a specific database table to use.
	 *
	 * @param string $databaseName
	 *
	 * @return mixed
	 */
	public function setDatabase(string $databaseName)
	{
		return false;
	}

	/**
	 * Returns the total number of rows affected by this query.
	 *
	 * @return mixed
	 */
	public function affectedRows(): int
	{
		return ibase_affected_rows($this->connID);
	}

	/**
	 * Insert ID
	 *
	 * @return	false
	 */
	public function insertID()
	{
		return false;
	}


	/**
	 * Generates the SQL for listing tables in a platform-dependent manner.
	 *
	 *
	 * @return false
	 */
	protected function _listTables($prefixLimit = false): string
	{
		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Generates a platform-specific query string so that the column names can be fetched.
	 *
	 * @return false
	 */
	protected function _listColumns(string $table = ''): string
	{
		return false;
	}


	/**
	 * Returns a string containing the version of the database being used.
	 *
	 * @return mixed
	 */
	public function getVersion()
	{
		if (($service = ibase_service_attach($this->hostname, $this->username, $this->password)) != FALSE) {
	    $server_info  = ibase_server_info($service, IBASE_SVC_SERVER_VERSION)
	                  . ' / '
	                  . ibase_server_info($service, IBASE_SVC_IMPLEMENTATION);
	    ibase_service_detach($service);
		} else {
			$server_info = ibase_errmsg();
		}

		return $server_info;
	}

	// --------------------------------------------------------------------

	/**
	 * Begin Transaction
	 *
	 * @return	bool
	 */
	protected function _trans_begin()
	{
		if (($trans_handle = ibase_trans($this->connID)) === FALSE)
		{
			return FALSE;
		}

		$this->_ibase_trans = $trans_handle;
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Commit Transaction
	 *
	 * @return	bool
	 */
	protected function _trans_commit()
	{
		if (ibase_commit($this->_ibase_trans))
		{
			$this->_ibase_trans = NULL;
			return TRUE;
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Rollback Transaction
	 *
	 * @return	bool
	 */
	protected function _trans_rollback()
	{
		if (ibase_rollback($this->_ibase_trans))
		{
			$this->_ibase_trans = NULL;
			return TRUE;
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Affected Rows
	 *
	 * @return	int
	 */
	public function affected_rows()
	{
		return ibase_affected_rows($this->connID);
	}

	// -------------------------------------------------------------------------------------------------------------------------------

	/**
	 * List table query
	 *
	 * Generates a platform-specific query string so that the table names can be fetched
	 *
	 * @param	bool	$prefix_limit
	 * @return	false
	 */
	protected function _list_tables($prefix_limit = FALSE)
	{
		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Show column query
	 *
	 * Generates a platform-specific query string so that the column names can be fetched
	 *
	 * @param	string	$table
	 * @return	false
	 */
	protected function _list_columns($table = '')
	{
		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Error
	 *
	 * Returns an array containing code and message of the last
	 * database error that has occured.
	 *
	 * @return	array
	 */
	public function error()
	{
		return array('code' => ibase_errcode(), 'message' => ibase_errmsg());
	}

	// --------------------------------------------------------------------

	/**
	 * Close DB Connection
	 *
	 * @return	void
	 */
	public function close()
	{
		ibase_close($this->connID);
	}
}
