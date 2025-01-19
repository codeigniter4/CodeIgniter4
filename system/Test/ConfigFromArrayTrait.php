<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\Exceptions\LogicException;

trait ConfigFromArrayTrait
{
    /**
     * Creates a Config instance from an array.
     *
     * @template T of \CodeIgniter\Config\BaseConfig
     *
     * @param class-string<T>      $classname Config classname
     * @param array<string, mixed> $config
     *
     * @return T
     */
    private function createConfigFromArray(string $classname, array $config)
    {
        $configObj = new $classname();

        foreach ($config as $key => $value) {
            if (property_exists($configObj, $key)) {
                $configObj->{$key} = $value;

                continue;
            }

            throw new LogicException(
                'No such property: ' . $classname . '::$' . $key,
            );
        }

        return $configObj;
    }
}
