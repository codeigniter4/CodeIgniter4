<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Validation\StrictRules;

use CodeIgniter\Validation\ValidationTest as TraditionalValidationTest;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 *
 * @group Others
 */
final class ValidationTest extends TraditionalValidationTest
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
        'login' => [
            'username' => [
                'label'  => 'Username',
                'rules'  => 'required',
                'errors' => [
                    'required' => 'custom username required error msg.',
                ],
            ],
            'password' => [
                'label'  => 'Password',
                'rules'  => 'required',
                'errors' => [
                    'required' => 'custom password required error msg.',
                ],
            ],
        ],
        'groupA_errors' => [
            'foo' => [
                'min_length' => 'Shame, shame. Too short.',
            ],
        ],
        'groupX'    => 'Not an array, so not a real group',
        'templates' => [
            'list'   => 'CodeIgniter\Validation\Views\list',
            'single' => 'CodeIgniter\Validation\Views\single',
        ],
    ];
}
