<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter;

use ReflectionClass;

/**
 * ComposerScripts
 *
 * These scripts are used by Composer during installs and updates
 * to move files to locations within the system folder so that end-users
 * do not need to use Composer to install a package, but can simply
 * download
 *
 * @codeCoverageIgnore
 * @package            CodeIgniter
 */
class ComposerScripts
{
	/**
	 * Base path to use.
	 *
	 * @var type
	 */
	protected static $basePath = 'ThirdParty/';

	/**
	 * After composer install/update, this is called to move
	 * the bare-minimum required files for our dependencies
	 * to appropriate locations.
	 *
	 * @throws \ReflectionException
	 */
	public static function postUpdate()
	{
		static::moveEscaper();
		static::buildKint();
	}

	//--------------------------------------------------------------------

	/**
	 * Move a file.
	 *
	 * @param string $source
	 * @param string $destination
	 *
	 * @return boolean
	 */
	protected static function moveFile(string $source, string $destination): bool
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

		return copy($source, $destination);
	}

	//--------------------------------------------------------------------

	/**
	 * Determine file path of a class.
	 *
	 * @param string $class
	 *
	 * @return string
	 * @throws \ReflectionException
	 */
	protected static function getClassFilePath(string $class)
	{
		$reflector = new ReflectionClass($class);

		return $reflector->getFileName();
	}

	//--------------------------------------------------------------------

	/**
	 * A recursive remove directory method.
	 *
	 * @param $dir
	 */
	protected static function removeDir($dir)
	{
		if (is_dir($dir))
		{
			$objects = scandir($dir);
			foreach ($objects as $object)
			{
				if ($object !== '.' && $object !== '..')
				{
					if (filetype($dir . '/' . $object) === 'dir')
					{
						static::removeDir($dir . '/' . $object);
					}
					else
					{
						unlink($dir . '/' . $object);
					}
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}

	/**
	 * Moves the Laminas Escaper files into our base repo so that it's
	 * available for packaged releases where the users don't user Composer.
	 *
	 * @throws \ReflectionException
	 */
	public static function moveEscaper()
	{
		if (class_exists('\\Laminas\\Escaper\\Escaper') && is_file(static::getClassFilePath('\\Laminas\\Escaper\\Escaper')))
		{
			$base = basename(__DIR__) . '/' . static::$basePath . 'Escaper';

			foreach ([$base, $base . '/Exception'] as $path)
			{
				if (! is_dir($path))
				{
					mkdir($path, 0755);
				}
			}

			$files = [
				static::getClassFilePath('\\Laminas\\Escaper\\Exception\\ExceptionInterface')       => $base . '/Exception/ExceptionInterface.php',
				static::getClassFilePath('\\Laminas\\Escaper\\Exception\\InvalidArgumentException') => $base . '/Exception/InvalidArgumentException.php',
				static::getClassFilePath('\\Laminas\\Escaper\\Exception\\RuntimeException')         => $base . '/Exception/RuntimeException.php',
				static::getClassFilePath('\\Laminas\\Escaper\\Escaper')                             => $base . '/Escaper.php',
			];

			foreach ($files as $source => $dest)
			{
				if (! static::moveFile($source, $dest))
				{
					die('Error moving: ' . $source);
				}
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Builds the kint.phar file into our base repo so that it's
	 * available for packaged releases where the users don't user Composer.
	 */
	public static function buildKint()
	{
		if (ini_get('phar.readonly'))
		{
			$iniFile = php_ini_loaded_file();
			echo 'Unable to build Kint' . PHP_EOL;
			echo 'You must disable phar.readonly in php.ini file located at:' . $iniFile . PHP_EOL;
			return;
		}

		$kintDir = 'vendor/kint-php/kint';

		if (is_dir($kintDir))
		{
			$base = basename(__DIR__) . '/' . static::$basePath . 'Kint';

			$outpath = $base . '/kint.phar';

			if (file_exists($outpath))
			{
				unlink($outpath);
			}

			$phar = new \Phar($outpath);
			$phar->setStub("<?php require 'phar://'.__FILE__.'/init_phar.php'; __HALT_COMPILER();");

			$pathlen = strlen($kintDir);

			$dirs = [
				$kintDir . '/src',
				$kintDir . '/resources/compiled',
			];

			$kintFiles = [];
			foreach ($dirs as $dir)
			{
				$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
				foreach ($iterator as $file)
				{
					if ($file->isDir())
					{
						continue;
					}
					$kintFiles[] = $file->getPathname();
				}
			}

			foreach ($kintFiles as $file)
			{
				$local = substr($file, $pathlen);
				$phar->addFile($file, $local);
			}

			$phar->addFile($base . '/init_phar.php', '/init_phar.php');
			$phar->addFile($kintDir . '/init.php', '/init.php');
			$phar->addFile($kintDir . '/init_helpers.php', '/init_helpers.php');
		}
	}
}
