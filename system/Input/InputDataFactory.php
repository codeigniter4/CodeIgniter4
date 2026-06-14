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

namespace CodeIgniter\Input;

/**
 * @see \CodeIgniter\Input\InputDataFactoryTest
 */
class InputDataFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): InputData
    {
        return new InputData($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createValidated(array $data): ValidatedInput
    {
        return new ValidatedInput($data);
    }
}
