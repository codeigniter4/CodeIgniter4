<?php

namespace CodeIgniter\Filters;

use CodeIgniter\Config\Services;
use CodeIgniter\Honeypot\Exceptions\HoneypotException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Honeypot;

/**
 * @backupGlobals enabled
 *
 * @internal
 */
final class HoneypotTest extends CIUnitTestCase
{
    protected $config;
    protected $honey;
    protected $request;
    protected $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new \Config\Filters();
        $this->honey  = new Honeypot();

        unset($_POST[$this->honey->name]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST[$this->honey->name] = 'hey';
    }

    //--------------------------------------------------------------------
    public function testBeforeTriggered()
    {
        $this->config->globals = [
            'before' => ['honeypot'],
            'after'  => [],
        ];

        $this->request  = Services::request(null, false);
        $this->response = Services::response();

        $filters = new Filters($this->config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        $this->expectException(HoneypotException::class);
        $request = $filters->run($uri, 'before');
    }

    //--------------------------------------------------------------------
    public function testBeforeClean()
    {
        $this->config->globals = [
            'before' => ['honeypot'],
            'after'  => [],
        ];

        unset($_POST[$this->honey->name]);
        $this->request  = Services::request(null, false);
        $this->response = Services::response();

        $expected = $this->request;

        $filters = new Filters($this->config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        $request = $filters->run($uri, 'before');
        $this->assertSame($expected, $request);
    }

    //--------------------------------------------------------------------

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testAfter()
    {
        $this->config->globals = [
            'before' => [],
            'after'  => ['honeypot'],
        ];

        $this->request  = Services::request(null, false);
        $this->response = Services::response();

        $filters = new Filters($this->config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        $this->response->setBody('<form></form>');
        $this->response = $filters->run($uri, 'after');
        $this->assertStringContainsString($this->honey->name, $this->response->getBody());
    }

    //--------------------------------------------------------------------

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testAfterNotApplicable()
    {
        $this->config->globals = [
            'before' => [],
            'after'  => ['honeypot'],
        ];

        $this->request  = Services::request(null, false);
        $this->response = Services::response();

        $filters = new Filters($this->config, $this->request, $this->response);
        $uri     = 'admin/foo/bar';

        $this->response->setBody('<div></div>');
        $this->response = $filters->run($uri, 'after');
        $this->assertStringNotContainsString($this->honey->name, $this->response->getBody());
    }
}
