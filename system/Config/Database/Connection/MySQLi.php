<?php namespace CodeIgniter\Config\Database\Connection;

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

/**
 * This is the base class to be used when configuring a connection to a MySQL database
 * using PHP's MySQLi extension. The application's database connection configuration
 * class should extend this class and configure the appropriate values to connect
 * to the database.
 */
class MySQLi extends \CodeIgniter\Config\Database\Connection
{
	/** @var string The character collation used in communicating with the database. */
	public $collation = 'utf8_general_ci';

	/** @var bool Whether client compression is enabled. */
	public $compressionEnabled = false;

	/**
	 * Whether to use the MySQL "delete hack" which allows the number of affected
	 * rows to be shown.
	 *
	 * Uses a preg_replace when enabled, adding a little more processing to all
	 * queries.
	 *
	 * @var bool
	 */
	public $deleteHack = true;

	/** @var string Path to the private key file for encryption. */
	public $sslKey;

	/** @var string Path to the public key certificate file for encryption. */
	public $sslCert;

	/** @var string Path to the certificate authority file for encryption. */
	public $sslCA;

	/**
	 * Path to a directory containing trusted CA certificates in PEM format.
	 *
	 * @var string
	 */
	public $sslCAPath;

	/**
	 * List of *allowed* ciphers to be used for encryption, separated by colons
	 * (':').
	 *
	 * @var string
	 */
	public $sslCipher;

	/** @var bool Whether to verify the server certificate. */
	public $sslVerify;

	/**
	 * The name of the adapter to be used by this connection.
	 * In most cases, this will match the name of the Connection class itself.
	 *
	 * This should not be modified/overridden by the application.
	 *
	 * @var string
	 */
	protected $adapter = 'MySQLi';
}
