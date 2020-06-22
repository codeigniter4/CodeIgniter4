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

namespace CodeIgniter\Modules;

/**
 * Modules Class
 *
 * @package CodeIgniter\Modules
 * @author  EllisLab Dev Team
 * @link 	https://codeigniter.com/user_guide/general/modules.html
 */
class Modules
{
	/**
	 * Auto-Discover
	 *
	 * @var boolean
	 */
	public $enabled = true;

	/**
	 * Auto-Discovery Within Composer Packages
	 *
	 * @var boolean
	 */
	public $discoverInComposer = true;

	/**
	 * Auto-Discover Rules Handler
	 *
	 * @var array
	 */
	public $aliases = [];

	/**
	 * Should the application auto-discover the requested resource.
	 *
	 * @param string $alias
	 *
	 * @return boolean
	 */
	public function shouldDiscover(string $alias): bool
	{
		if (! $this->enabled)
		{
			return false;
		}

		return in_array(strtolower($alias), $this->aliases);
	}
}
