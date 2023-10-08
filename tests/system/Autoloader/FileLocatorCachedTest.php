<?php

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

/**
 * @internal
 *
 * @group Others
 */
final class FileLocatorCachedTest extends FileLocatorTest
{
    private FileVarExportHandler $handler;
    protected FileLocator $locator;

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
}
