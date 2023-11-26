<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Mimes;

/**
 * @internal
 *
 * @group Others
 */
final class MimesTest extends CIUnitTestCase
{
    public static function provideGuessExtensionFromType(): iterable
    {
        return [
            'null' => [
                null,
                'xkadjflkjdsf',
            ],
            'single' => [
                'cpt',
                'application/mac-compactpro',
            ],
            'trimmed' => [
                'cpt',
                ' application/mac-compactpro ',
            ],
            'manyMimes' => [
                'csv',
                'text/csv',
            ],
            'mixedCase' => [
                'csv',
                'text/CSV',
            ],
        ];
    }

    /**
     * @dataProvider provideGuessExtensionFromType
     */
    public function testGuessExtensionFromType(?string $expected, string $mime): void
    {
        $this->assertSame($expected, Mimes::guessExtensionFromType($mime));
    }

    public static function provideGuessTypeFromExtension(): iterable
    {
        return [
            'null' => [
                null,
                'xalkjdlfkj',
            ],
            'single' => [
                'audio/midi',
                'mid',
            ],
            'many' => [
                'image/bmp',
                'bmp',
            ],
            'trimmed' => [
                'image/bmp',
                '.bmp',
            ],
            'mixedCase' => [
                'image/bmp',
                'BMP',
            ],
        ];
    }

    /**
     * @dataProvider provideGuessTypeFromExtension
     */
    public function testGuessTypeFromExtension(?string $expected, string $ext): void
    {
        $this->assertSame($expected, Mimes::guessTypeFromExtension($ext));
    }
}
