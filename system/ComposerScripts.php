<?php namespace CodeIgniter;

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

/**
 * ComposerScripts
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
		if (class_exists('\\Zend\\Escaper\\Escaper') && file_exists(self::getClassFilePath('\\Zend\\Escaper\\Escaper')))
		{
			if (! is_dir('system/View/Exception'))
			{
				mkdir('system/View/Exception', 0755);
			}

			$files = [
				self::getClassFilePath('\\Zend\\Escaper\\Exception\\ExceptionInterface') => './system/View/Exception/ExceptionInterface.php',
				self::getClassFilePath('\\Zend\\Escaper\\Exception\\InvalidArgumentException') => 'system/View/Exception/InvalidArgumentException.php',
				self::getClassFilePath('\\Zend\\Escaper\\Exception\\RuntimeException') => 'system/View/Exception/RuntimeException.php',
				self::getClassFilePath('\\Zend\\Escaper\\Escaper')=> 'system/View/Escaper.php'
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
	
	protected static function getClassFilePath( string $class )
	{
		$reflector = new \ReflectionClass($class);
		return $reflector->getFileName();
	}
	
	//--------------------------------------------------------------------

}
