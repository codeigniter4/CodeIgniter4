<?php namespace CodeIgniter\FTP;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
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
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * Class FTP
 *
 * @package CodeIgniter
 */
class FTP
{
	/**
	 * FTP Server hostname
	 *
	 * @var	string
	 */
	public $hostname = '';

	/**
	 * FTP Username
	 *
	 * @var	string
	 */
	public $username = '';

	/**
	 * FTP Password
	 *
	 * @var	string
	 */
	public $password = '';

	/**
	 * FTP Server port
	 *
	 * @var	int
	 */
	public $port = 21;

	/**
	 * Passive mode flag
	 *
	 * @var	bool
	 */
	public $passive	= true;

	/**
	 * Debug flag
	 *
	 * Specifies whether to display error messages.
	 *
	 * @var	bool
	 */
	public $debug = false;

	/**
	 * Connection timeout
	 *
	 * @var	int
	 */
	public $timeout	= 90;

	// --------------------------------------------------------------------

	/**
	 * Connection ID
	 *
	 * @var	resource
	 */
	protected $connId;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param	array	$config
	 * @return	void
	 */
	public function __construct(array $config = [])
	{
		empty($config) OR $this->initialize($config);
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize preferences
	 *
	 * @param	array	$config
	 * @return	void
	 */
	public function initialize(array $config = [])
	{
		if ($config)
		{
			foreach ($config as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}

		$this->hostname = preg_replace('|.+?://|', '', $this->hostname);
	}

	// --------------------------------------------------------------------

	/**
	 * FTP Connect
	 *
	 * @param	array	 $config	Connection values
	 * @return	bool
	 */
	public function connect(array $config = []): bool
	{
		if (count($config) > 0)
		{
			$this->initialize($config);
		}

		$this->connId = false;

		try {
			$this->connId = ftp_connect($this->hostname, $this->port);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}

		if ($this->connId === false)
		{
			if ($this->debug === true)
			{
				$this->_error('ftpUnableToConnect');
			}

			return false;
		}

		if (!$this->_login())
		{
			if ($this->debug === true)
			{
				$this->_error('ftpUnableToLogin');
			}

			return false;
		}

		// Set passive mode if needed
		if ($this->passive === true)
		{
			ftp_pasv($this->connId, true);
		}

		// Set timeout if needed
		if ($this->timeout)
		{
			ftp_set_option($this->connId, FTP_TIMEOUT_SEC, $this->timeout);
		}

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * FTP Login
	 *
	 * @return	bool
	 */
	protected function _login(): bool
	{
		$ftpLogin = false;

		try {
			$ftpLogin = ftp_login($this->connId, $this->username, $this->password);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}

		return $ftpLogin;
	}

	// --------------------------------------------------------------------

	/**
	 * Validates the connection ID
	 *
	 * @return	bool
	 */
	protected function _is_conn(): bool
	{
		if ( ! is_resource($this->connId))
		{
			if ($this->debug === true)
			{
				$this->_error('ftpNoConnection');
			}

			return false;
		}

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Change directory
	 *
	 * The second parameter lets us momentarily turn off debugging so that
	 * this function can be used to test for the existence of a folder
	 * without throwing an error. There's no FTP equivalent to is_dir()
	 * so we do it by trying to change to a particular directory.
	 * Internally, this parameter is only used by the "mirror" function below.
	 *
	 * @param	string	$path
	 * @param	bool	$suppress_debug
	 * @return	bool
	 */
	public function changedir(string $path, bool $suppress_debug = false): bool
	{
		if ( ! $this->_is_conn())
		{
			return false;
		}

		$chDir = false;

		try {
			$chDir = ftp_chdir($this->connId, $path);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}

		$result = $chDir;

		if ($result === false)
		{
			if ($this->debug === true && $suppress_debug === false)
			{
				$this->_error('ftpUnableToChangedir');
			}

			return false;
		}

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Create a directory
	 *
	 * @param	string	$path
	 * @param	int	$permissions
	 * @return	bool
	 */
	public function mkdir(string $path, bool $permissions = null): bool
	{
		if ($path === '' OR ! $this->_is_conn())
		{
			return false;
		}

		$mkDir = false;

		try {
			$mkDir = ftp_mkdir($this->connId, $path);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}

		$result = $mkDir;

		if ($result === false)
		{
			if ($this->debug === true)
			{
				$this->_error('ftpUnableToMkdir');
			}

			return false;
		}

		// Set file permissions if needed
		if ($permissions !== null)
		{
			$this->chmod($path, (int) $permissions);
		}

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Upload a file to the server
	 *
	 * @param	string	$locpath
	 * @param	string	$rempath
	 * @param	string	$mode
	 * @param	int	$permissions
	 * @return	bool
	 */
	public function upload(string $locpath, string $rempath, string $mode = 'auto', int $permissions = 0): bool
	{
		if ( ! $this->_is_conn())
		{
			return false;
		}

		if ( ! file_exists($locpath))
		{
			$this->_error('ftpNoSourceFile');
			return false;
		}

		// Set the mode if not specified
		if ($mode === 'auto')
		{
			// Get the file extension so we can set the upload type
			$ext 	= $this->_getext($locpath);
			$mode 	= $this->_settype($ext);
		}

		$mode = ($mode === 'ascii') ? FTP_ASCII : FTP_BINARY;

		$ftpPut = false;

		try {
			$ftpPut = ftp_put($this->connId, $rempath, $locpath, $mode);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}

		$result = $ftpPut;

		if ($result === false)
		{
			if ($this->debug === true)
			{
				$this->_error('ftpUnableToUpload');
			}

			return false;
		}

		// Set file permissions if needed
		if ($permissions !== null)
		{
			$this->chmod($rempath, (int) $permissions);
		}

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Download a file from a remote server to the local server
	 *
	 * @param	string	$rempath
	 * @param	string	$locpath
	 * @param	string	$mode
	 * @return	bool
	 */
	public function download(string $rempath, string $locpath, string $mode = 'auto'): bool
	{
		if ( ! $this->_is_conn())
		{
			return false;
		}

		// Set the mode if not specified
		if ($mode === 'auto')
		{
			// Get the file extension so we can set the upload type
			$ext 	= $this->_getext($rempath);
			$mode 	= $this->_settype($ext);
		}

		$mode = ($mode === 'ascii') ? FTP_ASCII : FTP_BINARY;

		$result = false;

		try {
			$result	= ftp_get($this->connId, $locpath, $rempath, $mode);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}

		if ($result === false)
		{
			if ($this->debug === true)
			{
				$this->_error('ftpUnableToDownload');
			}

			return false;
		}

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Rename (or move) a file
	 *
	 * @param	string	$old_file
	 * @param	string	$new_file
	 * @param	bool	$move
	 * @return	bool
	 */
	public function rename(string $old_file, string $new_file, bool $move = false): bool
	{
		if ( ! $this->_is_conn())
		{
			return false;
		}

		$ftpRename = false;

		try {
			$ftpRename = ftp_rename($this->connId, $old_file, $new_file);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}

		$result = $ftpRename;

		if ($result === false)
		{
			if ($this->debug === true)
			{
				$this->_error('ftpUnableTo'.($move === false ? 'Rename' : 'Move'));
			}

			return false;
		}

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Move a file
	 *
	 * @param	string	$old_file
	 * @param	string	$new_file
	 * @return	bool
	 */
	public function move(string $old_file, string $new_file): bool
	{
		return $this->rename($old_file, $new_file, true);
	}

	// --------------------------------------------------------------------

	/**
	 * Rename (or move) a file
	 *
	 * @param	string	$filepath
	 * @return	bool
	 */
	public function delete_file(string $filepath): bool
	{
		if ( ! $this->_is_conn())
		{
			return false;
		}

		$result = false;

		try {
			$result	= ftp_delete($this->connId, $filepath);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}

		if ($result === false)
		{
			if ($this->debug === true)
			{
				$this->_error('ftpUnableToDelete');
			}

			return false;
		}

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a folder and recursively delete everything (including sub-folders)
	 * contained within it.
	 *
	 * @param	string	$filepath
	 * @return	bool
	 */
	public function delete_dir(string $filepath): bool
	{
		if ( ! $this->_is_conn())
		{
			return false;
		}

		// Add a trailing slash to the file path if needed
		$filepath = preg_replace('/(.+?)\/*$/', '\\1/', $filepath);
		$list = $this->list_files($filepath);

		if ( ! empty($list))
		{
			for ($i = 0, $c = count($list); $i < $c; $i++)
			{
				// If we can't delete the item it's probably a directory,
				// so we'll recursively call delete_dir()
				try {
					if ( ! preg_match('#/\.\.?$#', $list[$i]) && ! ftp_delete($this->connId, $list[$i]))
					{
						$this->delete_dir($filepath.$list[$i]);
					}
				}
				catch (\Exception $e)
				{
					throw new \Exception($e->getMessage());
				}
			}
		}

		$rmDirStatus = false;

		try {
			$rmDirStatus = ftp_rmdir($this->connId, $filepath);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}

		if ($rmDirStatus === false)
		{
			if ($this->debug === true)
			{
				$this->_error('ftpUnableToDelete');
			}

			return false;
		}

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Set file permissions
	 *
	 * @param	string	$path	File path
	 * @param	int	$perm	Permissions
	 * @return	bool
	 */
	public function chmod(string $path, int $perm): bool
	{
		if ( ! $this->_is_conn())
		{
			return false;
		}

		$chmodStatus = false;

		try {
			$chmodStatus = ftp_chmod($this->connId, $perm, $path);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}

		if ($chmodStatus === false)
		{
			if ($this->debug === true)
			{
				$this->_error('ftpUnableToChmod');
			}

			return false;
		}

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * FTP List files in the specified directory
	 *
	 * @param	string	$path
	 * @return	array
	 */
	public function list_files(string $path = '.'): array
	{
		return $this->_is_conn()
			? ftp_nlist($this->connId, $path)
			: false;
	}

	// ------------------------------------------------------------------------

	/**
	 * Read a directory and recreate it remotely
	 *
	 * This function recursively reads a folder and everything it contains
	 * (including sub-folders) and creates a mirror via FTP based on it.
	 * Whatever the directory structure of the original file path will be
	 * recreated on the server.
	 *
	 * @param	string	$locpath	Path to source with trailing slash
	 * @param	string	$rempath	Path to destination - include the base folder with trailing slash
	 * @return	bool
	 */
	public function mirror(string $locpath, string $rempath): bool
	{
		if ( ! $this->_is_conn())
		{
			return false;
		}

		$openDir = false;

		try {
			$openDir = opendir($locpath);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}

		// Open the local file path
		if ($fp = $openDir)
		{
			// Attempt to open the remote file path and try to create it, if it doesn't exist
			if ( ! $this->changedir($rempath, true) && ( ! $this->mkdir($rempath) OR ! $this->changedir($rempath)))
			{
				return false;
			}

			// Recursively read the local directory
			while (false !== ($file = readdir($fp)))
			{
				if (is_dir($locpath.$file) && $file[0] !== '.')
				{
					$this->mirror($locpath.$file.'/', $rempath.$file.'/');
				}
				elseif ($file[0] !== '.')
				{
					// Get the file extension so we can se the upload type
					$ext 	= $this->_getext($file);
					$mode 	= $this->_settype($ext);

					$this->upload($locpath.$file, $rempath.$file, $mode);
				}
			}

			return true;
		}

		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Extract the file extension
	 *
	 * @param	string	$filename
	 * @return	string
	 */
	protected function _getext(string $filename): string
	{
		return (($dot = strrpos($filename, '.')) === false)
			? 'txt'
			: substr($filename, $dot + 1);
	}

	// --------------------------------------------------------------------

	/**
	 * Set the upload type
	 *
	 * @param	string	$ext	Filename extension
	 * @return	string
	 */
	protected function _settype(string $ext): string
	{
		return in_array($ext, ['txt', 'text', 'php', 'phps', 'php4', 'js', 'css', 'htm', 'html', 'phtml', 'shtml', 'log', 'xml'], true)
			? 'ascii'
			: 'binary';
	}

	// ------------------------------------------------------------------------

	/**
	 * Close the connection
	 *
	 * @return	bool
	 */
	public function close(): bool
	{
		$fpCloseStatus = false;

		if ($this->_is_conn())
		{
			try {
				$fpCloseStatus = ftp_close($this->connId);

				return $fpCloseStatus;
			}
			catch (\Exception $e)
			{
				throw new \Exception($e->getMessage());
			}
		}

		return false;
	}

	// ------------------------------------------------------------------------

	/**
	 * Display error message
	 *
	 * @param	string	$line
	 * @return	void
	 */
	protected function _error(string $line)
	{
		throw new \Exception(lang('Ftp.' . $line));
	}

	//--------------------------------------------------------------------

}