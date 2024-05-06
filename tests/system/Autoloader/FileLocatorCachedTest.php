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

namespace CodeIgniter\Autoloader;

use CodeIgniter\Cache\FactoriesCache\FileVarExportHandler;
use Config\Autoload;
use Config\Modules;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class FileLocatorCachedTest extends FileLocatorTest
{
    private FileVarExportHandler $handler;
    protected FileLocatorInterface $locator;

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        // Delete cache file.
        $autoloader  = new Autoloader();
        $handler     = new FileVarExportHandler();
        $fileLocator = new FileLocator($autoloader);
        $locator     = new FileLocatorCached($fileLocator, $handler);
        $locator->deleteCache();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $autoloader = new Autoloader();
        $autoloader->initialize(new Autoload(), new Modules());
        $autoloader->addNamespace([
            'Unknown'       => '/i/do/not/exist',
            'Tests/Support' => TESTPATH . '_support/',
            'App'           => APPPATH,
            'CodeIgniter'   => [
                TESTPATH,
                SYSTEMPATH,
            ],
            'Errors'              => APPPATH . 'Views/errors',
            'System'              => SUPPORTPATH . 'Autoloader/system',
            'CodeIgniter\\Devkit' => [
                TESTPATH . '_support/',
            ],
            'Acme\SampleProject' => TESTPATH . '_support',
            'Acme\Sample'        => TESTPATH . '_support/does/not/exists',
        ]);

        $this->handler = new FileVarExportHandler();
        $fileLocator   = new FileLocator($autoloader);
        $this->locator = new FileLocatorCached($fileLocator, $this->handler);
    }

    protected function tearDown(): void
    {
        $this->locator->__destruct();

        parent::tearDown();
    }

    public function testDeleteCache(): void
    {
        $this->assertNotSame([], $this->handler->get('FileLocatorCache'));

        $this->locator->deleteCache();

        $this->assertFalse($this->handler->get('FileLocatorCache'));
    }
}
