<?php

namespace CodeIgniter\Honeypot;

use CodeIgniter\Config\Services;
use CodeIgniter\Filters\Filters;
use CodeIgniter\Honeypot\Exceptions\HoneypotException;

/**
 * @backupGlobals enabled
 */
class HoneypotTest extends \CodeIgniter\Test\CIUnitTestCase
{

	protected $config;
	protected $honeypot;
	protected $request;
	protected $response;

	//--------------------------------------------------------------------

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

	//--------------------------------------------------------------------

	public function testAttachHoneypot()
	{
		$this->response->setBody('<form></form>');

		$this->honeypot->attachHoneypot($this->response);
		$this->assertStringContainsString($this->config->name, $this->response->getBody());

		$this->response->setBody('<div></div>');
		$this->assertStringNotContainsString($this->config->name, $this->response->getBody());
	}

	//--------------------------------------------------------------------

	public function testHasntContent()
	{
		unset($_POST[$this->config->name]);
		$this->request = Services::request();

		$this->assertEquals(false, $this->honeypot->hasContent($this->request));
	}

	public function testHasContent()
	{
		$this->assertEquals(true, $this->honeypot->hasContent($this->request));
	}

	//--------------------------------------------------------------------

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

	//--------------------------------------------------------------------
	public function testHoneypotFilterBefore()
	{
		$config = [
			'aliases' => ['trap' => '\CodeIgniter\Filters\Honeypot'],
			'globals' => [
				'before' => ['trap'],
				'after'  => [],
			],
		];

		$filters = new Filters((object) $config, $this->request, $this->response);
		$uri     = 'admin/foo/bar';

		$this->expectException(HoneypotException::class);
		$request = $filters->run($uri, 'before');
	}

	public function testHoneypotFilterAfter()
	{
		$config = [
			'aliases' => ['trap' => '\CodeIgniter\Filters\Honeypot'],
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

}
