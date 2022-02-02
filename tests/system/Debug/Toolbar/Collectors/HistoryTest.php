<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug\Toolbar\Collectors;

use CodeIgniter\Test\CIUnitTestCase;
use DateTime;

/**
 * @internal
 */
final class HistoryTest extends CIUnitTestCase
{
    private const STEP = 0.0001;

    protected float $time;

    protected function setUp(): void
    {
        parent::setUp();
        $this->time = number_format(microtime(true), 4, '.', '');
    }

    protected function createDummyDebugbarJson()
    {
        $time = $this->time;
        $path = WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . "debugbar_{$time}.json";

        $dummyData = [
            'vars' => [
                'response' => [
                    'statusCode'  => 200,
                    'contentType' => 'text/html; charset=UTF-8',
                ],
            ],
            'method' => 'get',
            'url'    => 'localhost',
            'isAJAX' => false,
        ];
        // create 10 dummy debugbar json files
        for ($i = 0; $i < 20; $i++) {
            $path = str_replace($time, number_format($time - self::STEP, 4, '.', ''), $path);
            file_put_contents($path, json_encode($dummyData));
            $time = number_format($time - self::STEP, 4, '.', '');
        }
    }

    protected function tearDown(): void
    {
        command('debugbar:clear');
    }

    public function testSetFiles()
    {
        $time = $this->time;
        // test dir is now populated with json
        $this->createDummyDebugbarJson();

        $history = new History();
        $history->setFiles($this->time, 20);
        $this->assertIsArray($history->display());
        $this->assertArrayHasKey('files', $history->display());
        $this->assertNotEmpty($history->display(), 'Dummy Debugbar data is empty');

        $time = number_format($time - self::STEP, 4, '.', '');

        foreach ($history->display()['files'] as $request) {
            $this->assertSame($request, [
                'time'        => $time,
                'datetime'    => DateTime::createFromFormat('U.u', $time)->format('Y-m-d H:i:s.u'),
                'active'      => false,
                'status'      => 200,
                'method'      => 'get',
                'url'         => 'localhost',
                'isAJAX'      => 'No',
                'contentType' => 'text/html; charset=UTF-8',

            ], 'Date format fail');
            $time = number_format($time - self::STEP, 4, '.', '');
        }
    }
}
