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

    public function testAddAddingAfter(): void
    {
        $replacer = new ContentReplacer();
        $content  = <<<'FILE'
            <?php

            namespace Config;

            $routes = Services::routes();

            if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
                require SYSTEMPATH . 'Config/Routes.php';
            }

            $routes->get('/', 'Home::index');

            /**
             * You will have access to the $routes object within that file without
             * needing to reload it.
             */

            FILE;

        $text    = 'service(\'auth\')->routes($routes);';
        $pattern = '/(.*)(\n' . preg_quote('$routes->', '/') . '[^\n]+?\n)/su';
        $replace = '$1$2' . "\n" . $text . "\n";
        $output  = $replacer->add($content, $text, $pattern, $replace);

        $expected = <<<'FILE'
            <?php

            namespace Config;

            $routes = Services::routes();

            if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
                require SYSTEMPATH . 'Config/Routes.php';
            }

            $routes->get('/', 'Home::index');

            service('auth')->routes($routes);

            /**
             * You will have access to the $routes object within that file without
             * needing to reload it.
             */

            FILE;
        $this->assertSame($expected, $output);
    }

    public function testAddAddingBefore(): void
    {
        $replacer = new ContentReplacer();
        $content  = <<<'FILE'
            <?php

            namespace App\Controllers;

            abstract class BaseController extends Controller
            {
                public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
                {
                    // Do Not Edit This Line
                    parent::initController($request, $response, $logger);
                }
            }
            FILE;

        $text    = '$this->helpers = array_merge($this->helpers, [\'auth\', \'setting\']);';
        $pattern = '/(' . preg_quote('// Do Not Edit This Line', '/') . ')/u';
        $replace = $text . "\n\n        " . '$1';
        $output  = $replacer->add($content, $text, $pattern, $replace);

        $expected = <<<'FILE'
            <?php

            namespace App\Controllers;

            abstract class BaseController extends Controller
            {
                public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
                {
                    $this->helpers = array_merge($this->helpers, ['auth', 'setting']);

                    // Do Not Edit This Line
                    parent::initController($request, $response, $logger);
                }
            }
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
        $output = $replacer->addAfter($content, $line, $after);

        $expected = <<<'FILE'
            $routes->get('/', 'Home::index');
            $routes->get('/login', 'Login::index');

            service('auth')->routes($routes);

            FILE;
        $this->assertSame($expected, $output);
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
        $output = $replacer->addBefore($content, $line, $before);

        $expected = <<<'FILE'
            <?php

            $this->helpers = array_merge($this->helpers, ['auth', 'setting']);
            // Do Not Edit This Line
            parent::initController($request, $response, $logger);
            // Do Not Edit This Line

            FILE;
        $this->assertSame($expected, $output);
    }
}
