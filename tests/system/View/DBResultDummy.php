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

namespace CodeIgniter\View;

use CodeIgniter\Database\MySQLi\Result;

// We need this for the _set_from_db_result() test
class DBResultDummy extends Result
{
    public function getFieldNames(): array
    {
        return [
            'name',
            'email',
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getResultArray(): array
    {
        return [
            [
                'name'  => 'John Doe',
                'email' => 'john@doe.com',
            ],
            [
                'name'  => 'Foo Bar',
                'email' => 'foo@bar.com',
            ],
        ];
    }
}
