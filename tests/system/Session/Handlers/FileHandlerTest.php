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

namespace CodeIgniter\Session\Handlers;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Session as SessionConfig;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class FileHandlerTest extends CIUnitTestCase
{
    private string $savePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->savePath = WRITEPATH . 'session_test';
        if (! is_dir($this->savePath)) {
            mkdir($this->savePath, 0700, true);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // clean up
        if (is_dir($this->savePath)) {
            $files = array_diff(scandir($this->savePath), ['.', '..']);

            foreach ($files as $file) {
                $path = $this->savePath . DIRECTORY_SEPARATOR . $file;
                if (is_link($path) || is_file($path)) {
                    @unlink($path);
                }
            }
            rmdir($this->savePath);
        }
    }

    public function testGcIgnoresSymlinks(): void
    {
        $config             = new SessionConfig();
        $config->savePath   = $this->savePath;
        $config->cookieName = 'ci_session';

        $handler = new FileHandler($config, '127.0.0.1');

        $sessionId   = '1234567890abcdef1234567890abcdef';
        $sessionFile = $this->savePath . DIRECTORY_SEPARATOR . 'ci_session' . $sessionId;

        $targetFile = $this->savePath . DIRECTORY_SEPARATOR . 'target.txt';
        file_put_contents($targetFile, 'target');
        touch($targetFile, time() - 10000);

        symlink($targetFile, $sessionFile);

        $this->assertTrue(is_link($sessionFile));

        $collected = $handler->gc(5000);

        $this->assertTrue(is_link($sessionFile));
        $this->assertSame(0, $collected);
    }

    public function testDestroyIgnoresSymlinks(): void
    {
        $config             = new SessionConfig();
        $config->savePath   = $this->savePath;
        $config->cookieName = 'ci_session';

        $handler = new FileHandler($config, '127.0.0.1');

        $sessionId = '1234567890abcdef1234567890abcdef';
        $handler->open($this->savePath, 'ci_session');

        $sessionFile = $this->savePath . DIRECTORY_SEPARATOR . 'ci_session' . $sessionId;

        $targetFile = $this->savePath . DIRECTORY_SEPARATOR . 'target.txt';
        file_put_contents($targetFile, 'target');

        symlink($targetFile, $sessionFile);

        $this->assertTrue(is_link($sessionFile));

        $result = $handler->destroy($sessionId);

        $this->assertTrue(is_link($sessionFile));
    }
}
