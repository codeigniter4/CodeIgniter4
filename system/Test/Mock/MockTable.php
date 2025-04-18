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

namespace CodeIgniter\Test\Mock;

use CodeIgniter\Exceptions\BadMethodCallException;
use CodeIgniter\View\Table;

class MockTable extends Table
{
    /**
     * Override inaccessible protected method
     *
     * @param string      $method
     * @param list<mixed> $params
     *
     * @return mixed
     */
    public function __call($method, $params)
    {
        if (is_callable([$this, '_' . $method])) {
            return call_user_func_array([$this, '_' . $method], $params);
        }

        throw new BadMethodCallException('Method ' . $method . ' was not found');
    }
}
