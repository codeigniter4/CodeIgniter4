<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Validation;

use CodeIgniter\Validation\StrictRules\DatabaseRelatedRulesTest as StrictDatabaseRelatedRulesTest;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 *
 * @group DatabaseLive
 */
final class DatabaseRelatedRulesTest extends StrictDatabaseRelatedRulesTest
{
    protected array $config = [
        'ruleSets' => [
            Rules::class,
            FormatRules::class,
            FileRules::class,
            CreditCardRules::class,
            TestRules::class,
        ],
        'groupA' => [
            'foo' => 'required|min_length[5]',
        ],
        'groupA_errors' => [
            'foo' => [
                'min_length' => 'Shame, shame. Too short.',
            ],
        ],
    ];

    protected function createRules()
    {
        return new Rules();
    }
}
