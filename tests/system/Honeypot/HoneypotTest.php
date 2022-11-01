<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Honeypot;

use CodeIgniter\Config\Services;
use CodeIgniter\Filters\Filters;
use CodeIgniter\Honeypot\Exceptions\HoneypotException;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group Others
 */
final class HoneypotTest extends CIUnitTestCase
{
    private $config;
    private $honeypot;
    private $request;
    private $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config   = new \Config\Honeypot();
        $this->honeypot = new Honeypot($this->config);

        unset($_POST[$this->config->name]);
        $_SERVER['REQUEST_METHOD']  = 'POST';
        $_POST[$this->config->name] = 'hey';
        $this->request              = Services::request(null, false);
        $this->response             = Services::response();
    }

    public function testAttachHoneypot()
    {
        $this->response->setBody('<form></form>');

        $this->honeypot->attachHoneypot($this->response);
        $this->assertStringContainsString($this->config->name, $this->response->getBody());

        $this->response->setBody('<div></div>');
        $this->assertStringNotContainsString($this->config->name, $this->response->getBody());
    }

    public function testAttachHoneypotAndContainer()
    {
        $this->response->setBody('<form></form>');
        $this->honeypot->attachHoneypot($this->response);
        $expected = '<form><div style="display:none"><label>Fill This Field</label><input type="text" name="honeypot" value=""/></div></form>';
        $this->assertSame($expected, $this->response->getBody());

        $this->config->container = '<div class="hidden">{template}</div>';
        $this->response->setBody('<form></form>');
        $this->honeypot->attachHoneypot($this->response);
        $expected = '<form><div class="hidden"><label>Fill This Field</label><input type="text" name="honeypot" value=""/></div></form>';
        $this->assertSame($expected, $this->response->getBody());
    }

    public function testHasntContent()
    {
        unset($_POST[$this->config->name]);
        $this->request = Services::request();

        $this->assertFalse($this->honeypot->hasContent($this->request));
    }

    public function testHasContent()
    {
        $this->assertTrue($this->honeypot->hasContent($this->request));
    }

    public function testConfigHidden()
    {
        $this->config->hidden = '';
        $this->expectException(HoneypotException::class);
        $this->honeypot = new Honeypot($this->config);
    }

    public function testConfigTemplate()
    {
        $this->config->template = '';
        $this->expectException(HoneypotException::class);
        $this->honeypot = new Honeypot($this->config);
    }

    public function testConfigName()
    {
        $this->config->name = '';
        $this->expectException(HoneypotException::class);
        $this->honeypot = new Honeypot($this->config);
    }

    public function testHoneypotFilterBefore()
    {
        $config = [
            'aliases' => ['trap' => \CodeIgniter\Filters\Honeypot::class],
            'globals' => [
                'before' => ['trap'],
                'after'  => [],
            ],
        ];

        $filters = new Filters((object) $config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        $this->expectException(HoneypotException::class);
        $filters->run($uri, 'before');
    }

    public function testHoneypotFilterAfter()
    {
        $config = [
            'aliases' => ['trap' => \CodeIgniter\Filters\Honeypot::class],
            'globals' => [
                'before' => [],
                'after'  => ['trap'],
            ],
        ];

        $filters = new Filters((object) $config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        $this->response->setBody('<form></form>');
        $this->response = $filters->run($uri, 'after');
        $this->assertStringContainsString($this->config->name, $this->response->getBody());
    }

    public function testEmptyConfigContainer()
    {
        $config            = new \Config\Honeypot();
        $config->container = '';
        $honeypot          = new Honeypot($config);

        $this->assertSame(
            '<div style="display:none">{template}</div>',
            $this->getPrivateProperty($honeypot, 'config')->container
        );
    }

    public function testNoTemplateConfigContainer()
    {
        $config            = new \Config\Honeypot();
        $config->container = '<div></div>';
        $honeypot          = new Honeypot($config);

        $this->assertSame(
            '<div style="display:none">{template}</div>',
            $this->getPrivateProperty($honeypot, 'config')->container
        );
    }
}
