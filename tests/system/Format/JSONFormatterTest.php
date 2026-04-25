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

namespace CodeIgniter\Format;

use CodeIgniter\Config\Services;
use CodeIgniter\EnvironmentDetector;
use CodeIgniter\Format\Exceptions\FormatException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class JSONFormatterTest extends CIUnitTestCase
{
    private JSONFormatter $jsonFormatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jsonFormatter = new JSONFormatter();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Services::resetSingle('environment');
    }

    /**
     * @param array<string, string> $data
     */
    #[DataProvider('provideFormattingToJson')]
    public function testFormattingToJson(array $data, string $expected): void
    {
        $this->assertSame($expected, $this->jsonFormatter->format($data));
    }

    /**
     * @return iterable<string, array{0: array<string, string>, 1: string}>
     */
    public static function provideFormattingToJson(): iterable
    {
        yield 'empty array' => [[], '[]'];

        yield 'simple array' => [['foo' => 'bar'], "{\n    \"foo\": \"bar\"\n}"];

        yield 'unicode array' => [['foo' => 'База данни грешка'], "{\n    \"foo\": \"База данни грешка\"\n}"];

        yield 'url array' => [['foo' => 'https://www.example.com/foo/bar'], "{\n    \"foo\": \"https://www.example.com/foo/bar\"\n}"];
    }

    public function testJSONFormatterThrowsError(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded');

        $this->assertSame('Boom', $this->jsonFormatter->format(["\xB1\x31"]));
    }

    public function testFormattingToJsonIsCompactInProduction(): void
    {
        Services::injectMock('environment', new EnvironmentDetector('production'));

        $this->assertSame('{"foo":"bar"}', $this->jsonFormatter->format(['foo' => 'bar']));
    }
}
