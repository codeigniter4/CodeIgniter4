<?php namespace CodeIgniter;

/**
 * Class ComposerScripts
 *
 * These scripts are used by Composer during installs and updates
 * to move files to locations within the system folder so that end-users
 * do not need to use Composer to install a package, but can simply
 * download
 *
 * @codeCoverageIgnore
 * @package CodeIgniter
 */
class ComposerScripts
{
	/**
	 * After composer install/update, this is called to move
	 * the bare-minimum required files for our dependencies
	 * to appropriate locations.
	 */
	public static function postUpdate()
	{
		/*
		 * Zend/Escaper
		 */
		if (file_exists('vendor/zendframework/zend-escaper/src/Escaper.php'))
		{
			if (! is_dir('system/View/Exception'))
			{
				mkdir('system/View/Exception', 0755);
			}

			$files = [
				'./vendor/zendframework/zend-escaper/src/Exception/ExceptionInterface.php' => './system/View/Exception/ExceptionInterface.php',
				'vendor/zendframework/zend-escaper/src/Exception/InvalidArgumentException.php' => 'system/View/Exception/InvalidArgumentException.php',
				'vendor/zendframework/zend-escaper/src/Exception/RuntimeException.php' => 'system/View/Exception/RuntimeException.php',
				'vendor/zendframework/zend-escaper/src/Escaper.php' => 'system/View/Escaper.php'
			];

			foreach ($files as $source => $dest)
			{
				if (! self::moveFile($source, $dest))
				{
					die('Error moving: '. $source);
				}
			}
		}
	}

	//--------------------------------------------------------------------

	protected static function moveFile(string $source, string $destination)
	{
		$source = realpath($source);

		if (empty($source))
		{
			die('Cannot move file. Source path invalid.');
		}

		if (! is_file($source))
		{
			return false;
		}

		return rename($source, $destination);
	}

	//--------------------------------------------------------------------

}
