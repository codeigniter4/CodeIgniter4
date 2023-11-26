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
final class ContentReplacerTest extends CIUnitTestCase
{
    public function testReplace(): void
    {
        $replacer = new ContentReplacer();
        $content  = <<<'FILE'
            <?php

            namespace CodeIgniter\Shield\Config;

            use CodeIgniter\Config\BaseConfig;
            use CodeIgniter\Shield\Models\UserModel;

            class Auth extends BaseConfig
            {
            FILE;

        $replaces = [
            'namespace CodeIgniter\Shield\Config'    => 'namespace Config',
            "use CodeIgniter\\Config\\BaseConfig;\n" => '',
            'extends BaseConfig'                     => 'extends \\CodeIgniter\\Shield\\Config\\Auth',
        ];
        $output = $replacer->replace($content, $replaces);

        $expected = <<<'FILE'
            <?php

            namespace Config;

            use CodeIgniter\Shield\Models\UserModel;

            class Auth extends \CodeIgniter\Shield\Config\Auth
            {
            FILE;
        $this->assertSame($expected, $output);
    }

    public function testAddAfter(): void
    {
        $replacer = new ContentReplacer();
        $content  = <<<'FILE'
            $routes->get('/', 'Home::index');
            $routes->get('/login', 'Login::index');

            FILE;

        $line   = "\n" . 'service(\'auth\')->routes($routes);';
        $after  = '$routes->';
        $result = $replacer->addAfter($content, $line, $after);

        $expected = <<<'FILE'
            $routes->get('/', 'Home::index');
            $routes->get('/login', 'Login::index');

            service('auth')->routes($routes);

            FILE;
        $this->assertSame($expected, $result);
    }

    public function testAddAfterAlreadyUpdated(): void
    {
        $replacer = new ContentReplacer();
        $content  = <<<'FILE'
            $routes->get('/', 'Home::index');
            $routes->get('/login', 'Login::index');

            service('auth')->routes($routes);

            FILE;

        $line   = "\n" . 'service(\'auth\')->routes($routes);';
        $after  = '$routes->';
        $result = $replacer->addAfter($content, $line, $after);
        $this->assertNull($result);
    }

    public function testAddBefore(): void
    {
        $replacer = new ContentReplacer();
        $content  = <<<'FILE'
            <?php

            // Do Not Edit This Line
            parent::initController($request, $response, $logger);
            // Do Not Edit This Line

            FILE;

        $line   = '$this->helpers = array_merge($this->helpers, [\'auth\', \'setting\']);';
        $before = '// Do Not Edit This Line';
        $result = $replacer->addBefore($content, $line, $before);

        $expected = <<<'FILE'
            <?php

            $this->helpers = array_merge($this->helpers, ['auth', 'setting']);
            // Do Not Edit This Line
            parent::initController($request, $response, $logger);
            // Do Not Edit This Line

            FILE;
        $this->assertSame($expected, $result);
    }

    public function testAddBeforeAlreadyUpdated(): void
    {
        $replacer = new ContentReplacer();
        $content  = <<<'FILE'
            <?php

            $this->helpers = array_merge($this->helpers, ['auth', 'setting']);
            // Do Not Edit This Line
            parent::initController($request, $response, $logger);
            // Do Not Edit This Line

            FILE;

        $line   = '$this->helpers = array_merge($this->helpers, [\'auth\', \'setting\']);';
        $before = '// Do Not Edit This Line';
        $result = $replacer->addBefore($content, $line, $before);
        $this->assertNull($result);
    }
}
