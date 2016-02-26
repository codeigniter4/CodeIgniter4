<?php namespace CodeIgniter\Data\Database\MySQLi;

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
 * @package	  CodeIgniter
 * @author	  CodeIgniter Dev Team
 * @copyright Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	  http://opensource.org/licenses/MIT	MIT License
 * @link	  http://codeigniter.com
 * @since	  Version 4.0.0
 * @filesource
 */

class Connection extends \CodeIgniter\Data\Database\Connection
{
	protected $errorCode;
	protected $errorMessage;
	protected $id = false;
	protected $mysqli;

	private $clientFlags = 0;
	private $hostname;
	private $port;
	private $socket;

	public function __construct(\CodeIgniter\Config\Database\Connection $config)
	{
		parent::__construct($config);
	}

	public function connect(): bool
	{
		$this->configureConnection();
		$this->initializeConnection();

		if ( ! $this->mysqli)
		{
			throw new \Exception('mysqli_init failed.');
		}

		if ($this->mysqli->real_connect(
			$this->hostname,
			$this->connectionConfig->username,
			$this->connectionConfig->password,
			$this->connectionConfig->database,
			$this->port,
			$this->socket,
			$this->clientFlags
		))
		{
			if ($this->clientFlags & MYSQLI_CLIENT_SSL)
			{
				$this->verifySSLConnection();
			}

			$this->id = $this->mysqli;
			return true;
		}

		return false;
	}

	public function disconnect()
	{
		if ($this->id !== false)
		{
			$this->id->close();
		}

		$this->id = false;
	}

	public function reconnect()
	{
		if ($this->id !== false && $this->id->ping() === false)
		{
			$this->id = false;
		}
	}

	public function getErrorCode()
	{
		if ($this->id === false)
		{
			return isset($this->errorCode) ? $this->errorCode : null;
		}

		return empty($this->id->connect_errno) ? $this->id->errno : $this->id->connect_errno;
	}

	public function getErrorMessage()
	{
		if ($this->id === false)
		{
			return isset($this->errorMessage) ? $this->errorMessage : null;
		}

		return empty($this->id->connect_errno) ? $this->id->error : $this->id->connect_error;
	}

	/**
	 * Set connection settings which can be configured before calling mysqli_init().
	 */
	protected function configureConnection()
	{
		// Do we have a socket path?
		if ($this->connectionConfig->hostname[0] === '/')
		{
			$this->hostname = null;
			$this->port = null;
			$this->socket = $this->connectionConfig->hostname;
		}
		else
		{
			$this->hostname = $this->connectionConfig->persistentConnectionEnabled === true
				? "p:{$this->connectionConfig->hostname}" : $this->connectionConfig->hostname;
			$this->port = empty($this->connectionConfig->port) ? null : $this->connectionConfig->port;
			$this->socket = null;
		}

		if ($this->connectionConfig->compressionEnabled === true)
		{
			$this->clientFlags |= MYSQLI_CLIENT_COMPRESS;
		}
	}

	/**
	 * Initialize MySQLi and set additional configuration options before the connection
	 * is established.
	 */
	protected function initializeConnection()
	{
		$this->mysqli = mysqli_init();
		$this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
		if (isset($this->connectionConfig->strictSQLEnabled))
		{
			$this->mysqli->options(
				MYSQLI_INIT_COMMAND,
				$this->connectionConfig->strictSQLEnabled ?
					'SET SESSION sql_mode = CONCAT(@@sql_mode, ",", "STRICT_ALL_TABLES")'
					: 'SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
						@@sql_mode,
						"STRICT_ALL_TABLES,", ""),
						",STRICT_ALL_TABLES", ""),
						"STRICT_ALL_TABLES", ""),
						"STRICT_TRANS_TABLES,", ""),
						",STRICT_TRANS_TABLES", ""),
						"STRICT_TRANS_TABLES", "")'
			);
		}

		$ssl = [];
		empty($this->connectionConfig->sslKey)    or $ssl['key']    = $this->connectionConfig->sslKey;
		empty($this->connectionConfig->sslCert)   or $ssl['cert']   = $this->connectionConfig->sslCert;
		empty($this->connectionConfig->sslCA)     or $ssl['ca']     = $this->connectionConfig->sslCA;
		empty($this->connectionConfig->sslCAPath) or $ssl['capath'] = $this->connectionConfig->sslCAPath;
		empty($this->connectionConfig->sslCipher) or $ssl['cipher'] = $this->connectionConfig->sslCipher;

		if ( ! empty($ssl))
		{
			$this->initializeSSLConnection($ssl);
		}
	}

	protected function initializeSSLConnection(array $ssl)
	{
		if (isset($this->connectionConfig->sslVerify))
		{
			if ($this->connectionConfig->sslVerify)
			{
				defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT') &&
					$this->mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
			}
			elseif (defined('MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT'))
			{
				// Apparently (when it exists), setting MYSQLI_OPT_SSL_VERIFY_SERVER_CERT
				// to FALSE didn't do anything, so PHP 5.6.16 introduced yet another
				// constant...
				//
				// https://secure.php.net/ChangeLog-5.php#5.6.16
				// https://bugs.php.net/bug.php?id=68344
				$this->_mysqli->options(MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT, TRUE);
			}
		}

		$this->clientFlags |= MYSQLI_CLIENT_SSL;
		$this->mysqli->ssl_set(
			isset($ssl['key'])    ? $ssl['key']    : null,
			isset($ssl['cert'])   ? $ssl['cert']   : null,
			isset($ssl['ca'])     ? $ssl['ca']     : null,
			isset($ssl['capath']) ? $ssl['capath'] : null,
			isset($ssl['cipher']) ? $ssl['cipher'] : null
		);
	}

	/**
	 * In some older MySQL clients, the an unencrypted connection is silently accepted
	 * if SSL setup fails. This method will throw an exception if that occurs.
	 *
	 * @throws \Exception
	 * @return void
	 */
	protected function verifySSLConnection()
	{
		// The MySQL Native Driver in PHP7 should not return an unencrypted connection
		// when configured to use SSL.
		if (strpos($this->mysqli->client_info, 'mysqlnd ') !== false)
		{
			return;
		}

		// If using libmysqlclient greater than 5.7.3, the real_connect() call
		// would have failed if an SSL connection could not be established.
		//
		// Note: from 5.7.4 on, if the client was distributed with MySQL server,
		// the version number should match the server's version number. Connector/C,
		// will use a different number. For instance, 6.1.3 is the Connector/C client
		// which matches the version distributed with MySQL Server 5.7.4.
		//
		// http://dev.mysql.com/doc/relnotes/connector-c/en/news-6-1-3.html
		if (version_compare($this->mysqli->client_info, '5.7.3', '>'))
		{
			return;
		}

		// Issue a query to check whether the connection was made using SSL.
		if (empty($this->mysqli->query("SHOW STATUS LIKE 'ssl_cipher'")->fetch_object()->Value))
		{
			$this->mysqli->close();
			throw new \Exception('MySQLi was configured for an SSL connection, but got an unencrypted connection instead!');
		}
	}
}
