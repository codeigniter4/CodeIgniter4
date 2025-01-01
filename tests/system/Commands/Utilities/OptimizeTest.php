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

namespace CodeIgniter\Commands\Utilities;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class OptimizeTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        $this->resetServices();

        parent::setUp();
    }

    protected function getBuffer(): string
    {
        return $this->getStreamFilterBuffer();
    }

    public function testEnableConfigCaching(): void
    {
        $command = new Optimize(service('logger'), service('commands'));

        $runCaching = $this->getPrivateMethodInvoker($command, 'runCaching');

        // private function runCaching(?bool $enableConfigCache, ?bool $enableLocatorCache, ?bool $disable): void
        $runCaching(true, null, null);

        // Check if config caching is enabled
        $this->assertFileContains('public bool $configCacheEnabled = true;', APPPATH . 'Config/Optimize.php');
    }

    public function testEnableLocatorCaching(): void
    {
        $command = new Optimize(service('logger'), service('commands'));

        $runCaching = $this->getPrivateMethodInvoker($command, 'runCaching');

        // private function runCaching(?bool $enableConfigCache, ?bool $enableLocatorCache, ?bool $disable): void
        $runCaching(null, true, null);

        // Check if locator caching is enabled
        $this->assertFileContains('public bool $locatorCacheEnabled = true;', APPPATH . 'Config/Optimize.php');
    }

    public function testDisableCaching(): void
    {
        $command = new Optimize(service('logger'), service('commands'));

        $runCaching = $this->getPrivateMethodInvoker($command, 'runCaching');

        // private function runCaching(?bool $enableConfigCache, ?bool $enableLocatorCache, ?bool $disable): void
        $runCaching(null, null, true);

        // Check if both caches are disabled
        $this->assertFileContains('public bool $configCacheEnabled = false;', APPPATH . 'Config/Optimize.php');
        $this->assertFileContains('public bool $locatorCacheEnabled = false;', APPPATH . 'Config/Optimize.php');
    }

    public function testWithoutOptions(): void
    {
        $command = new Optimize(service('logger'), service('commands'));

        $runCaching = $this->getPrivateMethodInvoker($command, 'runCaching');

        // private function runCaching(?bool $enableConfigCache, ?bool $enableLocatorCache, ?bool $disable): void
        $runCaching(null, null, null);

        // Check if both caches are disabled
        $this->assertFileContains('public bool $configCacheEnabled = true;', APPPATH . 'Config/Optimize.php');
        $this->assertFileContains('public bool $locatorCacheEnabled = true;', APPPATH . 'Config/Optimize.php');
    }

    protected function assertFileContains(string $needle, string $filePath): void
    {
        $this->assertFileExists($filePath);
        $this->assertStringContainsString($needle, file_get_contents($filePath));
    }
}
