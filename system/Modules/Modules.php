<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Modules;

/**
 * Modules Class
 *
 * @see https://codeigniter.com/user_guide/general/modules.html
 *
 * @phpstan-consistent-constructor
 */
class Modules
{
    /**
     * Auto-Discover
     *
     * @var bool
     */
    public $enabled = true;

    /**
     * Auto-Discovery Within Composer Packages
     *
     * @var bool
     */
    public $discoverInComposer = true;

    /**
     * Auto-Discover Rules Handler
     *
     * @var list<string>
     */
    public $aliases = [];

    public function __construct()
    {
        // For @phpstan-consistent-constructor
    }

    /**
     * Should the application auto-discover the requested resource.
     */
    public function shouldDiscover(string $alias): bool
    {
        if (! $this->enabled) {
            return false;
        }

        return in_array(strtolower($alias), $this->aliases, true);
    }

    public static function __set_state(array $array)
    {
        $obj = new static();

        $properties = array_keys(get_object_vars($obj));

        foreach ($properties as $property) {
            $obj->{$property} = $array[$property];
        }

        return $obj;
    }
}
