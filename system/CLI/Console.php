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

namespace CodeIgniter\CLI;

use CodeIgniter\CodeIgniter;

/**
 * Console
 */
class Console
{

	/**
	 * Main CodeIgniter instance.
	 *
	 * @var CodeIgniter
	 */
	protected $app;

	//--------------------------------------------------------------------

	/**
	 * Console constructor.
	 *
	 * @param \CodeIgniter\CodeIgniter $app
	 */
	public function __construct(CodeIgniter $app)
	{
		$this->app = $app;
	}

	//--------------------------------------------------------------------

	/**
	 * Runs the current command discovered on the CLI.
	 *
	 * @param boolean $useSafeOutput
	 *
	 * @return \CodeIgniter\HTTP\RequestInterface|\CodeIgniter\HTTP\Response|\CodeIgniter\HTTP\ResponseInterface|mixed
	 * @throws \Exception
	 */
	public function run(bool $useSafeOutput = false)
	{
		$path = CLI::getURI() ?: 'list';

		// Set the path for the application to route to.
		$this->app->setPath("ci{$path}");

		return $this->app->useSafeOutput($useSafeOutput)->run();
	}

	//--------------------------------------------------------------------

	/**
	 * Displays basic information about the Console.
	 */
	public function showHeader()
	{
		CLI::newLine(1);

		CLI::write(CLI::color('CodeIgniter CLI Tool', 'green')
				. ' - Version ' . CodeIgniter::CI_VERSION
				. ' - Server-Time: ' . date('Y-m-d H:i:sa'));

		CLI::newLine(1);
	}

	//--------------------------------------------------------------------
}
