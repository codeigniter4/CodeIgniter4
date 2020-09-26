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

namespace CodeIgniter\Commands\Cache;

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;

/**
 * Shows information on the cache.
 */
class InfoCache extends BaseCommand
{
	/**
	 * Command grouping.
	 *
	 * @var string
	 */
	protected $group = 'Cache';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'cache:info';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Shows file cache information in the current system.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'cache:info';

	/**
	 * Clears the cache
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$config = config('Cache');
		helper('number');

		if ($config->handler !== 'file')
		{
			CLI::error('This command only supports the file cache handler.');

			return;
		}

		$cache  = CacheFactory::getHandler($config);
		$caches = $cache->getCacheInfo();
		$tbody  = [];

		foreach ($caches as $key => $field)
		{
			$tbody[] = [
				$key,
				clean_path($field['server_path']),
				number_to_size($field['size']),
				Time::createFromTimestamp($field['date']),
			];
		}

		$thead = [
			CLI::color('Name', 'green'),
			CLI::color('Server Path', 'green'),
			CLI::color('Size', 'green'),
			CLI::color('Date', 'green'),
		];

		CLI::table($tbody, $thead);
	}
}
