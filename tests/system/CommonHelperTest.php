<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter;

use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Tests\Support\Autoloader\FatalLocator;

/**
 * @internal
 *
 * @group Others
 *
 * @covers ::helper
 */
final class CommonHelperTest extends CIUnitTestCase
{
    private array $dummyHelpers = [
        APPPATH . 'Helpers' . DIRECTORY_SEPARATOR . 'foobarbaz_helper.php',
        SYSTEMPATH . 'Helpers' . DIRECTORY_SEPARATOR . 'foobarbaz_helper.php',
    ];

    protected function setUp(): void
    {
        $this->resetServices();
        parent::setUp();
        $this->cleanUpDummyHelpers();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanUpDummyHelpers();
        $this->resetServices();
    }

    private function createDummyHelpers(): void
    {
        $text = <<<'PHP'
            <?php

            if (! function_exists('foo_bar_baz')) {
            	function foo_bar_baz(): string
            	{
            		return __FILE__;
            	}
            }

            PHP;

        foreach ($this->dummyHelpers as $helper) {
            file_put_contents($helper, $text);
        }
    }

    private function cleanUpDummyHelpers(): void
    {
        foreach ($this->dummyHelpers as $helper) {
            if (is_file($helper)) {
                unlink($helper);
            }
        }
    }

    /**
     * @return FileLocator&MockObject
     */
    private function getMockLocator()
    {
        return $this->getMockBuilder(FileLocator::class)
            ->setConstructorArgs([Services::autoloader()])
            ->onlyMethods(['search'])
            ->getMock();
    }

    public function testHelperWithFatalLocatorThrowsException(): void
    {
        // Replace the locator with one that will fail if it is called
        $locator = new FatalLocator(Services::autoloader());
        Services::injectMock('locator', $locator);

        try {
            helper('baguette');
            $exception = false;
        } catch (RuntimeException $e) {
            $exception = true;
        }

        $this->assertTrue($exception);
    }

    public function testHelperLoadsOnce(): void
    {
        // Load it the first time
        helper('baguette');

        // Replace the locator with one that will fail if it is called
        $locator = new FatalLocator(Services::autoloader());
        Services::injectMock('locator', $locator);

        try {
            helper('baguette');
            $exception = false;
        } catch (RuntimeException $e) {
            $exception = true;
        }

        $this->assertFalse($exception);
    }

    public function testHelperLoadsAppHelperFirst(): void
    {
        foreach ($this->dummyHelpers as $helper) {
            $this->assertFileDoesNotExist($helper, sprintf(
                'The dummy helper file "%s" should not be existing before it is tested.',
                $helper
            ));
        }

        $this->createDummyHelpers();
        $locator = $this->getMockLocator();
        $locator->method('search')->with('Helpers/foobarbaz_helper')->willReturn($this->dummyHelpers);
        Services::injectMock('locator', $locator);

        helper('foobarbaz');

        // this chunk is not needed really; just added so that IDEs will be happy
        if (! function_exists('foo_bar_baz')) {
            function foo_bar_baz(): string
            {
                return __FILE__;
            }
        }

        $this->assertSame($this->dummyHelpers[0], foo_bar_baz());
    }
}
