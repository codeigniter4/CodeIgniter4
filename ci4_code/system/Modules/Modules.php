<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Modules;

/**
 * Modules Class
 *
 * @link https://codeigniter.com/user_guide/general/modules.html
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

		return in_array(strtolower($alias), $this->aliases, true);
	}
}
