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
use CodeIgniter\Test\StreamFilterTrait;
use DateTime;

/**
 * @internal
 *
 * @group Others
 */
final class HistoryTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private const STEP = 0.000001;

    private float $time;

    protected function setUp(): void
    {
        parent::setUp();

        $this->time = (float) sprintf('%.6f', microtime(true));
    }

    protected function tearDown(): void
    {
        command('debugbar:clear');

        parent::tearDown();
    }

    private function createDummyDebugbarJson(): void
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

        // create 20 dummy debugbar json files
        for ($i = 0; $i < 20; $i++) {
            $path = str_replace($time, sprintf('%.6f', $time - self::STEP), $path);
            file_put_contents($path, json_encode($dummyData));
            $time = sprintf('%.6f', $time - self::STEP);
        }
    }

    public function testSetFiles(): void
    {
        $time = $this->time;

        // test dir is now populated with json
        $this->createDummyDebugbarJson();

        $activeRowTime = $time = sprintf('%.6f', $time - self::STEP);

        $history = new History();
        $history->setFiles($time, 20);

        $this->assertArrayHasKey('files', $history->display());
        $this->assertNotEmpty($history->display()['files'], 'Dummy Debugbar data is empty');

        foreach ($history->display()['files'] as $request) {
            $this->assertSame($request['time'], sprintf('%.6f', $time));
            $this->assertSame(
                $request['datetime'],
                DateTime::createFromFormat('U.u', $time)->format('Y-m-d H:i:s.u')
            );
            $this->assertSame($request['active'], ($time === $activeRowTime));

            $time = sprintf('%.6f', $time - self::STEP);
        }
    }
}
