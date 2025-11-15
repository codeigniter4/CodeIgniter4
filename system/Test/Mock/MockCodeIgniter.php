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

use CodeIgniter\CodeIgniter;

class MockCodeIgniter extends CodeIgniter
{
    protected ?string $context = 'web';

    /**
     * @param int $code
     *
     * @deprecated 4.4.0 No longer Used. Moved to index.php.
     */
    protected function callExit($code)
    {
        // Do not call exit() in testing.
    }
}
