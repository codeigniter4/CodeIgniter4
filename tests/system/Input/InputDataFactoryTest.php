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

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class InputDataFactoryTest extends CIUnitTestCase
{
    public function testCreateReturnsInputData(): void
    {
        $factory = new InputDataFactory();
        $input   = $factory->create(['page' => '2']);

        $this->assertInstanceOf(InputData::class, $input);
        $this->assertSame(2, $input->integer('page'));
    }

    public function testCreateReturnsNewInputDataInstances(): void
    {
        $factory = new InputDataFactory();

        $this->assertNotSame($factory->create([]), $factory->create([]));
    }

    public function testCreateValidatedReturnsValidatedInput(): void
    {
        $factory = new InputDataFactory();
        $input   = $factory->createValidated(['page' => '2']);

        $this->assertInstanceOf(ValidatedInput::class, $input);
        $this->assertSame(2, $input->integer('page'));
    }

    public function testCreateValidatedReturnsNewValidatedInputInstances(): void
    {
        $factory = new InputDataFactory();

        $this->assertNotSame($factory->createValidated([]), $factory->createValidated([]));
    }
}
