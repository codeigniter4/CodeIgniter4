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

use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class InputDataTest extends CIUnitTestCase
{
    public function testGetReturnsInputFieldValue(): void
    {
        $input = new InputData(['title' => 'Hello World']);

        $this->assertSame('Hello World', $input->get('title'));
    }

    public function testGetReturnsDefaultForMissingInputField(): void
    {
        $input = new InputData([]);

        $this->assertSame('fallback', $input->get('title', 'fallback'));
    }

    public function testHasReturnsTrueForNullInputField(): void
    {
        $input = new InputData(['note' => null]);

        $this->assertTrue($input->has('note'));
        $this->assertNull($input->get('note', 'fallback'));
    }

    public function testGetAndHasSupportDotSyntax(): void
    {
        $input = new InputData([
            'post' => [
                'meta' => [
                    'slug' => 'hello-world',
                ],
            ],
        ]);

        $this->assertSame('hello-world', $input->get('post.meta.slug'));
        $this->assertTrue($input->has('post.meta.slug'));
    }

    public function testStringReturnsInputString(): void
    {
        $input = new InputData(['title' => 'Hello World']);

        $this->assertSame('Hello World', $input->string('title'));
    }

    public function testStringReturnsDefaultForMissingInputField(): void
    {
        $input = new InputData([]);

        $this->assertSame('Untitled', $input->string('title', 'Untitled'));
    }

    public function testStringThrowsForInvalidInputValue(): void
    {
        $input = new InputData(['title' => 123]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The input "title" value cannot be read as string.');

        $input->string('title');
    }

    public function testIntegerReturnsInputInteger(): void
    {
        $input = new InputData(['page' => '15']);

        $this->assertSame(15, $input->integer('page'));
    }

    public function testIntegerReturnsDefaultForMissingInputField(): void
    {
        $input = new InputData([]);

        $this->assertSame(1, $input->integer('page', 1));
    }

    public function testIntegerSupportsDotSyntax(): void
    {
        $input = new InputData(['filters' => ['page' => '2']]);

        $this->assertSame(2, $input->integer('filters.page'));
    }

    public function testIntegerThrowsForInvalidInputValue(): void
    {
        $input = new InputData(['page' => '1.5']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The input "page" value cannot be read as integer.');

        $input->integer('page');
    }

    public function testFloatReturnsInputFloat(): void
    {
        $input = new InputData(['price' => '15.50']);

        $this->assertEqualsWithDelta(15.50, $input->float('price'), PHP_FLOAT_EPSILON);
    }

    public function testFloatReturnsInputIntegerAsFloat(): void
    {
        $input = new InputData(['price' => 15]);

        $this->assertEqualsWithDelta(15.0, $input->float('price'), PHP_FLOAT_EPSILON);
    }

    public function testFloatReturnsDefaultForMissingInputField(): void
    {
        $input = new InputData([]);

        $this->assertEqualsWithDelta(1.5, $input->float('price', 1.5), PHP_FLOAT_EPSILON);
    }

    public function testFloatThrowsForInvalidInputValue(): void
    {
        $input = new InputData(['price' => 'free']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The input "price" value cannot be read as float.');

        $input->float('price');
    }

    public function testBooleanReturnsInputBoolean(): void
    {
        $input = new InputData(['active' => 'true']);

        $this->assertTrue($input->boolean('active'));
    }

    public function testBooleanReturnsFalseForInputFalseString(): void
    {
        $input = new InputData(['active' => 'false']);

        $this->assertFalse($input->boolean('active'));
    }

    public function testBooleanReturnsDefaultForMissingInputField(): void
    {
        $input = new InputData([]);

        $this->assertFalse($input->boolean('active', false));
    }

    public function testBooleanThrowsForInvalidInputValue(): void
    {
        $input = new InputData(['active' => 'sometimes']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The input "active" value cannot be read as boolean.');

        $input->boolean('active');
    }

    public function testArrayReturnsInputArray(): void
    {
        $input = new InputData(['tags' => ['php', 'ci']]);

        $this->assertSame(['php', 'ci'], $input->array('tags'));
    }

    public function testArrayReturnsDefaultForMissingInputField(): void
    {
        $input = new InputData([]);

        $this->assertSame(['draft'], $input->array('tags', ['draft']));
    }

    public function testArrayThrowsForInvalidInputValue(): void
    {
        $input = new InputData(['tags' => 'php']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The input "tags" value cannot be read as array.');

        $input->array('tags');
    }

    public function testTypedAccessorsReturnNullForNullInputFields(): void
    {
        $input = new InputData([
            'title'  => null,
            'page'   => null,
            'price'  => null,
            'active' => null,
            'tags'   => null,
        ]);

        $this->assertNull($input->string('title', 'Untitled'));
        $this->assertNull($input->integer('page', 1));
        $this->assertNull($input->float('price', 1.5));
        $this->assertNull($input->boolean('active', false));
        $this->assertNull($input->array('tags', ['draft']));
    }
}
