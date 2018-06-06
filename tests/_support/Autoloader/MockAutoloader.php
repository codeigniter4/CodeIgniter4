<?php namespace Tests\Support\Autoloader;

use CodeIgniter\Autoloader\Autoloader;

class MockAutoloader extends Autoloader
{

	protected $files = [];

	//--------------------------------------------------------------------

	public function setFiles($files)
	{
		$this->files = $files;
	}

	//--------------------------------------------------------------------

	protected function requireFile($file)
	{
		return in_array($file, $this->files) ? $file : false;
	}

	//--------------------------------------------------------------------
}
