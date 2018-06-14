<?php namespace CodeIgniter\Config;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * Class Config
 *
 * @package CodeIgniter\Config
 */
class Config
{
	/**
	 * Cache for instance of any configurations that
	 * have been requested as "shared" instance.
	 *
	 * @var array
	 */
	static private $instances = [];

	/**
	 * @param string $name
	 * @param bool   $getShared
	 *
	 * @return mixed|null
	 */
	public static function get(string $name, bool $getShared = true)
	{
		if (! $getShared)
		{
			return self::createClass( $name);
		}

		if( !isset( self::$instances[$name] ) )
		{
			self::$instances[$name] = self::createClass($name);
		}
		return self::$instances[$name];
	}

	/**
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	private static function createClass(string $name)
	{
		if( class_exists( $name ) )
		{
			return new $name();
		}

		$locator = Services::locator();
		$file = $locator->locateFile($name,'Config');

		if (empty($file))
		{
			return null;
		}

		$classname = self::getClassname($file);

		/*if (strpos( $classname, 'App\\' ) !== false)
		{

		}*/
		return new $classname();
	}

	/**
	 * Examines a file and returns the fully qualified domain name.
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	private static function getClassname(string $file) : string
	{
		$php    = file_get_contents($file);
		$tokens = token_get_all($php);
		$count  = count($tokens);
		$dlm    = false;
		$namespace = '';
		$class_name = '';

		for ($i = 2; $i < $count; $i++)
		{
			if ((isset($tokens[$i-2][1]) && ($tokens[$i-2][1] == "phpnamespace" || $tokens[$i-2][1] == "namespace")) || ($dlm && $tokens[$i-1][0] == T_NS_SEPARATOR && $tokens[$i][0] == T_STRING))
			{
				if (! $dlm)
				{
					$namespace = 0;
				}
				if (isset($tokens[$i][1]))
				{
					$namespace = $namespace ? $namespace."\\".$tokens[$i][1] : $tokens[$i][1];
					$dlm       = true;
				}
			}
			elseif ($dlm && ($tokens[$i][0] != T_NS_SEPARATOR) && ($tokens[$i][0] != T_STRING))
			{
				$dlm = false;
			}
			if (($tokens[$i-2][0] == T_CLASS || (isset($tokens[$i-2][1]) && $tokens[$i-2][1] == "phpclass"))
				&& $tokens[$i-1][0] == T_WHITESPACE
				&& $tokens[$i][0] == T_STRING)
			{
				$class_name = $tokens[$i][1];
				break;
			}
		}

		return $namespace .'\\'. $class_name;
	}
}