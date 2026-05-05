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

namespace CodeIgniter\HTTP;

use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\I18n\Time;
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
    public function testGetReturnsValidatedFieldValue(): void
    {
        $input = new ValidatedInput(['title' => 'Hello World']);

        $this->assertSame('Hello World', $input->get('title'));
    }

    public function testGetReturnsDefaultForMissingValidatedField(): void
    {
        $input = new ValidatedInput([]);

        $this->assertSame('fallback', $input->get('title', 'fallback'));
    }

    public function testHasReturnsTrueForNullValidatedField(): void
    {
        $input = new ValidatedInput(['note' => null]);

        $this->assertTrue($input->has('note'));
        $this->assertNull($input->get('note', 'fallback'));
    }

    public function testGetAndHasSupportDotSyntax(): void
    {
        $input = new ValidatedInput([
            'post' => [
                'meta' => [
                    'slug' => 'hello-world',
                ],
            ],
        ]);

        $this->assertSame('hello-world', $input->get('post.meta.slug'));
        $this->assertTrue($input->has('post.meta.slug'));
    }

    public function testIntegerReturnsValidatedInteger(): void
    {
        $input = new ValidatedInput(['page' => '15']);

        $this->assertSame(15, $input->integer('page'));
    }

    public function testIntegerReturnsDefaultForMissingValidatedField(): void
    {
        $input = new ValidatedInput([]);

        $this->assertSame(1, $input->integer('page', 1));
    }

    public function testIntegerSupportsDotSyntax(): void
    {
        $input = new ValidatedInput(['filters' => ['page' => '2']]);

        $this->assertSame(2, $input->integer('filters.page'));
    }

    public function testIntegerThrowsForInvalidValidatedValue(): void
    {
        $input = new ValidatedInput(['page' => '1.5']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The validated "page" value cannot be read as integer.');

        $input->integer('page');
    }

    public function testBooleanReturnsValidatedBoolean(): void
    {
        $input = new ValidatedInput(['active' => 'true']);

        $this->assertTrue($input->boolean('active'));
    }

    public function testBooleanReturnsFalseForValidatedFalseString(): void
    {
        $input = new ValidatedInput(['active' => 'false']);

        $this->assertFalse($input->boolean('active'));
    }

    public function testBooleanReturnsDefaultForMissingValidatedField(): void
    {
        $input = new ValidatedInput([]);

        $this->assertFalse($input->boolean('active', false));
    }

    public function testBooleanThrowsForInvalidValidatedValue(): void
    {
        $input = new ValidatedInput(['active' => 'sometimes']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The validated "active" value cannot be read as boolean.');

        $input->boolean('active');
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
            'page'         => null,
            'active'       => null,
            'published_at' => null,
            'status'       => null,
        ]);

        $this->assertNull($input->integer('page', 1));
        $this->assertNull($input->boolean('active', false));
        $this->assertNotInstanceOf(Time::class, $input->date('published_at'));
        $this->assertNotInstanceOf(StatusEnum::class, $input->enum('status', StatusEnum::class, StatusEnum::PENDING));
    }
}
