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
use Config\App;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ConfigCheckTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        $this->resetServices();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->resetServices();
        parent::tearDown();
    }

    protected function getBuffer()
    {
        return $this->getStreamFilterBuffer();
    }

    public function testCommandConfigCheckNoArg(): void
    {
        command('config:check');

        $this->assertStringContainsString(
            'You must specify a Config classname.',
            $this->getBuffer()
        );
    }

    public function testCommandConfigCheckApp(): void
    {
        command('config:check App');

        $this->assertStringContainsString(App::class, $this->getBuffer());
        $this->assertStringContainsString("public 'baseURL", $this->getBuffer());
    }

    public function testCommandConfigCheckNonexistentClass(): void
    {
        command('config:check Nonexistent');

        $this->assertStringContainsString(
            'No such Config class: Nonexistent',
            $this->getBuffer()
        );
    }

    public function testGetKintD(): void
    {
        $command  = new ConfigCheck(service('logger'), service('commands'));
        $getKintD = $this->getPrivateMethodInvoker($command, 'getKintD');

        $output = $getKintD(new App());

        $output = preg_replace(
            '/(\033\[[0-9;]+m)|(\035\[[0-9;]+m)/u',
            '',
            $output
        );

        $this->assertStringContainsString(
            'Config\App#',
            $output
        );
        $this->assertStringContainsString(
            <<<'EOL'
                (
                    public 'baseURL' -> string (19) "http://example.com/"
                    public 'allowedHostnames' -> array (0) []
                    public 'indexPage' -> string (9) "index.php"
                    public 'uriProtocol' -> string (11) "REQUEST_URI"
                    public 'permittedURIChars' -> string (14) "a-z 0-9~%.:_\-"
                    public 'defaultLocale' -> string (2) "en"
                    public 'negotiateLocale' -> boolean false
                    public 'supportedLocales' -> array (1) [
                        0 => string (2) "en"
                    ]
                    public 'appTimezone' -> string (3) "UTC"
                    public 'charset' -> string (5) "UTF-8"
                    public 'forceGlobalSecureRequests' -> boolean false
                    public 'proxyIPs' -> array (0) []
                    public 'CSPEnabled' -> boolean false
                EOL,
            $output
        );
    }

    public function testGetVarDump(): void
    {
        $command    = new ConfigCheck(service('logger'), service('commands'));
        $getVarDump = $this->getPrivateMethodInvoker($command, 'getVarDump');

        $output = $getVarDump(new App());

        if (
            ini_get('xdebug.mode')
            && in_array(
                'develop',
                explode(',', ini_get('xdebug.mode')),
                true
            )
        ) {
            // Xdebug force adds colors on xdebug.cli_color=2
            $output = preg_replace(
                '/(\033\[[0-9;]+m)|(\035\[[0-9;]+m)/u',
                '',
                $output
            );

            // Xdebug overloads var_dump().
            $this->assertStringContainsString(
                'class Config\App#',
                $output
            );
            $this->assertStringContainsString(
                <<<'EOL'
                    {
                      public string $baseURL =>
                      string(19) "http://example.com/"
                      public array $allowedHostnames =>
                      array(0) {
                      }
                      public string $indexPage =>
                      string(9) "index.php"
                      public string $uriProtocol =>
                      string(11) "REQUEST_URI"
                      public string $permittedURIChars =>
                      string(14) "a-z 0-9~%.:_\-"
                      public string $defaultLocale =>
                      string(2) "en"
                      public bool $negotiateLocale =>
                      bool(false)
                      public array $supportedLocales =>
                      array(1) {
                        [0] =>
                        string(2) "en"
                      }
                      public string $appTimezone =>
                      string(3) "UTC"
                      public string $charset =>
                      string(5) "UTF-8"
                      public bool $forceGlobalSecureRequests =>
                      bool(false)
                      public array $proxyIPs =>
                      array(0) {
                      }
                      public bool $CSPEnabled =>
                      bool(false)
                    }
                    EOL,
                $output
            );
        } else {
            // PHP's var_dump().
            $this->assertStringContainsString(
                'object(Config\App)#',
                $output
            );
            $this->assertStringContainsString(
                <<<'EOL'
                    {
                      ["baseURL"]=>
                      string(19) "http://example.com/"
                      ["allowedHostnames"]=>
                      array(0) {
                      }
                      ["indexPage"]=>
                      string(9) "index.php"
                      ["uriProtocol"]=>
                      string(11) "REQUEST_URI"
                      ["permittedURIChars"]=>
                      string(14) "a-z 0-9~%.:_\-"
                      ["defaultLocale"]=>
                      string(2) "en"
                      ["negotiateLocale"]=>
                      bool(false)
                      ["supportedLocales"]=>
                      array(1) {
                        [0]=>
                        string(2) "en"
                      }
                      ["appTimezone"]=>
                      string(3) "UTC"
                      ["charset"]=>
                      string(5) "UTF-8"
                      ["forceGlobalSecureRequests"]=>
                      bool(false)
                      ["proxyIPs"]=>
                      array(0) {
                      }
                      ["CSPEnabled"]=>
                      bool(false)
                    }
                    EOL,
                $output
            );
        }
    }
}
