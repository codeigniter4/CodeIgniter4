<?php namespace CodeIgniter\Test\Mock;

/**
 * Class MockFileLogger
 *
 * Extends FileHandler, exposing some inner workings
 */

class MockFileLogger extends \CodeIgniter\Log\Handlers\FileHandler
{
	/**
	 * Where would the log be written?
	 */
	public $destination;

	//--------------------------------------------------------------------

	public function __construct(array $config)
	{
		parent::__construct($config);
		$this->handles     = $config['handles'] ?? [];
		$this->destination = $this->path . 'log-' . date('Y-m-d') . '.' . $this->fileExtension;
	}

}
