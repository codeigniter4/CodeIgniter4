<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Publisher;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 *
 * @group Others
 */
final class PublisherContentReplaceTest extends CIUnitTestCase
{
    private string $file;
    private Publisher $publisher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->file = __DIR__ . '/App.php';
        copy(APPPATH . 'Config/App.php', $this->file);

        $this->publisher = new Publisher(__DIR__, __DIR__);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unlink($this->file);
    }

    public function testAddLineAfter(): void
    {
        $result = $this->publisher->addLineAfter(
            $this->file,
            '    public int $myOwnConfig = 1000;',
            'public bool $CSPEnabled = false;'
        );

        $this->assertTrue($result);
        $this->assertStringContainsString(
            '    public bool $CSPEnabled = false;
    public int $myOwnConfig = 1000;',
            file_get_contents($this->file)
        );
    }

    public function testAddLineBefore(): void
    {
        $result = $this->publisher->addLineBefore(
            $this->file,
            '    public int $myOwnConfig = 1000;',
            'public bool $CSPEnabled = false;'
        );

        $this->assertTrue($result);
        $this->assertStringContainsString(
            '    public int $myOwnConfig = 1000;
    public bool $CSPEnabled = false;',
            file_get_contents($this->file)
        );
    }

    public function testReplace(): void
    {
        $result = $this->publisher->replace(
            $this->file,
            [
                'use CodeIgniter\Config\BaseConfig;' . "\n" => '',
                'class App extends BaseConfig'              => 'class App extends \Some\Package\SomeConfig',
            ]
        );

        $this->assertTrue($result);
        $this->assertStringNotContainsString(
            'use CodeIgniter\Config\BaseConfig;',
            file_get_contents($this->file)
        );
        $this->assertStringContainsString(
            'class App extends \Some\Package\SomeConfig',
            file_get_contents($this->file)
        );
        $this->assertStringNotContainsString(
            'class App extends BaseConfig',
            file_get_contents($this->file)
        );
    }
}
