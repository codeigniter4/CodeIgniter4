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
 */
final class MimesTest extends CIUnitTestCase
{
    public function extensionsList()
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
     * @dataProvider extensionsList
     *
     * @param $expected
     * @param $ext
     * @param mixed $mime
     */
    public function testGuessExtensionFromType($expected, $mime)
    {
        $this->assertSame($expected, Mimes::guessExtensionFromType($mime));
    }

    public function mimesList()
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
     * @dataProvider mimesList
     *
     * @param mixed $expected
     * @param mixed $ext
     */
    public function testGuessTypeFromExtension($expected, $ext)
    {
        $this->assertSame($expected, Mimes::guessTypeFromExtension($ext));
    }
}
