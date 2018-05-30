<?php namespace Tests\Support\Autoloader;

use CodeIgniter\Autoloader\FileLocator;

class MockFileLocator extends FileLocator
{

	protected $files = [];

	//--------------------------------------------------------------------

	public function setFiles($files)
	{
		$this->files = $files;
	}

	//--------------------------------------------------------------------

	protected function requireFile(string $file): bool
	{
		return in_array($file, $this->files) ? $file : false;
	}

	//--------------------------------------------------------------------
}
