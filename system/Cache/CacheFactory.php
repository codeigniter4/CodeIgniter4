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

namespace CodeIgniter\Cache;

use CodeIgniter\Cache\Exceptions\CacheException;
use CodeIgniter\Exceptions\CriticalError;

/**
 * Class Cache
 *
 * A factory for loading the desired
 *
 * @package CodeIgniter\Cache
 */
class CacheFactory
{
	/**
	 * Attempts to create the desired cache handler, based upon the
	 *
	 * @param \Config\Cache $config
	 * @param string|null   $handler
	 * @param string|null   $backup
	 *
	 * @return \CodeIgniter\Cache\CacheInterface
	 */
	public static function getHandler($config, string $handler = null, string $backup = null)
	{
		if (! isset($config->validHandlers) || ! is_array($config->validHandlers))
		{
			throw CacheException::forInvalidHandlers();
		}

		if (! isset($config->handler) || ! isset($config->backupHandler))
		{
			throw CacheException::forNoBackup();
		}

		$handler = ! empty($handler) ? $handler : $config->handler;
		$backup  = ! empty($backup) ? $backup : $config->backupHandler;

		if (! array_key_exists($handler, $config->validHandlers) || ! array_key_exists($backup, $config->validHandlers))
		{
			throw CacheException::forHandlerNotFound();
		}

		// Get an instance of our handler.
		$adapter = new $config->validHandlers[$handler]($config);

		if (! $adapter->isSupported())
		{
			$adapter = new $config->validHandlers[$backup]($config);

			if (! $adapter->isSupported())
			{
				// Log stuff here, don't throw exception. No need to raise a fuss.
				// Fall back to the dummy adapter.
				$adapter = new $config->validHandlers['dummy']();
			}
		}

		// If $adapter->initialization throws a CriticalError exception, we will attempt to
		// use the $backup handler, if that also fails, we resort to the dummy handler.
		try
		{
			$adapter->initialize();
		}
		catch (CriticalError $e)
		{
			// log the fact that an exception occurred as well what handler we are resorting to
			log_message('critical', $e->getMessage() . ' Resorting to using ' . $backup . ' handler.');

			// get the next best cache handler (or dummy if the $backup also fails)
			$adapter = self::getHandler($config, $backup, 'dummy');
		}

		return $adapter;
	}

	//--------------------------------------------------------------------
}
