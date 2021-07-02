<?php

namespace CodeIgniter\Debug;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class ExceptionsTest extends CIUnitTestCase
{
    public function testNew()
    {
        $actual = new Exceptions(new \Config\Exceptions(), Services::request(), Services::response());
        $this->assertInstanceOf(Exceptions::class, $actual);
    }

    /**
     * @dataProvider dirtyPathsProvider
     */
    public function testCleanPaths($file, $expected)
    {
        $this->assertSame($expected, Exceptions::cleanPath($file));
    }

    public function dirtyPathsProvider()
    {
        $ds = DIRECTORY_SEPARATOR;

        return [
            [
                APPPATH . 'Config' . $ds . 'App.php',
                'APPPATH' . $ds . 'Config' . $ds . 'App.php',
            ],
            [
                SYSTEMPATH . 'CodeIgniter.php',
                'SYSTEMPATH' . $ds . 'CodeIgniter.php',
            ],
            [
                VENDORPATH . 'autoload.php',
                'VENDORPATH' . $ds . 'autoload.php',
            ],
            [
                FCPATH . 'index.php',
                'FCPATH' . $ds . 'index.php',
            ],
        ];
    }
}
