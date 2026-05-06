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

namespace CodeIgniter\Validation;

use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Input\InputData;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Enum\ColorEnum;
use Tests\Support\Enum\RoleEnum;
use Tests\Support\Enum\StatusEnum;

/**
 * @internal
 */
#[Group('Others')]
final class ValidatedInputTest extends CIUnitTestCase
{
    public function testValidatedInputExtendsInputData(): void
    {
        $input = new ValidatedInput(['page' => '15']);

        $this->assertInstanceOf(InputData::class, $input);
        $this->assertSame(15, $input->integer('page'));
    }

    public function testDateReturnsValidatedTime(): void
    {
        $input = new ValidatedInput(['published_at' => '2026-05-04']);

        $this->assertInstanceOf(Time::class, $input->date('published_at'));
        $this->assertSame('2026-05-04', $input->date('published_at')->toDateString());
    }

    public function testDateSupportsCustomFormat(): void
    {
        $input = new ValidatedInput(['published_at' => '04/05/2026']);

        $this->assertSame('2026-05-04', $input->date('published_at', 'd/m/Y')->toDateString());
    }

    public function testDateThrowsForInvalidValidatedValue(): void
    {
        $input = new ValidatedInput(['published_at' => '']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The validated "published_at" value cannot be read as date.');

        $input->date('published_at');
    }

    public function testEnumReturnsValidatedStringBackedEnum(): void
    {
        $input = new ValidatedInput(['status' => 'active']);

        $this->assertSame(StatusEnum::ACTIVE, $input->enum('status', StatusEnum::class));
    }

    public function testEnumReturnsValidatedIntBackedEnum(): void
    {
        $input = new ValidatedInput(['role' => '2']);

        $this->assertSame(RoleEnum::ADMIN, $input->enum('role', RoleEnum::class));
    }

    public function testEnumReturnsValidatedUnitEnum(): void
    {
        $input = new ValidatedInput(['color' => 'GREEN']);

        $this->assertSame(ColorEnum::GREEN, $input->enum('color', ColorEnum::class));
    }

    public function testEnumReturnsDefaultForMissingValidatedField(): void
    {
        $input = new ValidatedInput([]);

        $this->assertSame(StatusEnum::PENDING, $input->enum('status', StatusEnum::class, StatusEnum::PENDING));
    }

    public function testEnumThrowsForDefaultFromDifferentEnumClass(): void
    {
        $input = new ValidatedInput([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The validated "status" value cannot be read as Tests\Support\Enum\StatusEnum.');

        $input->enum('status', StatusEnum::class, ColorEnum::GREEN);
    }

    public function testEnumThrowsForInvalidValidatedValue(): void
    {
        $input = new ValidatedInput(['status' => 'archived']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The validated "status" value cannot be read as Tests\Support\Enum\StatusEnum.');

        $input->enum('status', StatusEnum::class);
    }

    public function testTypedAccessorsReturnNullForNullValidatedFields(): void
    {
        $input = new ValidatedInput([
            'published_at' => null,
            'status'       => null,
        ]);

        $this->assertNotInstanceOf(Time::class, $input->date('published_at'));
        $this->assertNotInstanceOf(StatusEnum::class, $input->enum('status', StatusEnum::class, StatusEnum::PENDING));
    }
}
