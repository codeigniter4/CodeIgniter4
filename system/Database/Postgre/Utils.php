<?php namespace CodeIgniter\Database\Postgre;

class Utils extends \CodeIgniter\Database\BaseUtils
{
	/**
	 * List databases statement
	 *
	 * @var	string
	 */
	protected $listDatabases = 'SELECT datname FROM pg_database';

	/**
	 * OPTIMIZE TABLE statement
	 *
	 * @var	string
	 */
	protected $optimizeTable = 'REINDEX TABLE %s';

	//--------------------------------------------------------------------

	/**
	 * Platform dependent version of the backup function.
	 *
	 * @param array|null $prefs
	 *
	 * @return mixed
	 */
	public function _backup(array $prefs = null)
	{
		throw new DatabaseException('Unsupported feature of the database platform you are using.');
	}

	//--------------------------------------------------------------------
}
