<?php

use CodeIgniter\HTTP\UserAgent;

class UserAgent_test extends \CIUnitTestCase {

	protected $_user_agent = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; en-us) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27';
	protected $_mobile_ua  = 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_1 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8B117 Safari/6531.22.7';

	/**
	 * @var \CodeIgniter\HTTP\UserAgent
	 */
	protected $agent;

	protected function setUp()
	{
		parent::setUp();

		// set a baseline user agent
		$_SERVER['HTTP_USER_AGENT'] = $this->_user_agent;

		$this->agent = new UserAgent();

		helper('url');
	}

	// --------------------------------------------------------------------

	public function testMobile()
	{
		// Mobile Not Set
		$_SERVER['HTTP_USER_AGENT'] = $this->_mobile_ua;
		$this->assertEquals('', $this->agent->isMobile());
		unset($_SERVER['HTTP_USER_AGENT']);
	}

	// --------------------------------------------------------------------

	public function testIsFunctions()
	{
		$this->assertTrue($this->agent->isBrowser());
		$this->assertTrue($this->agent->isBrowser('Safari'));
		$this->assertFalse($this->agent->isBrowser('Firefox'));
		$this->assertFalse($this->agent->isRobot());
		$this->assertFalse($this->agent->isMobile());
	}

	// --------------------------------------------------------------------

	public function testReferrer()
	{
		$_SERVER['HTTP_REFERER'] = 'http://codeigniter.com/user_guide/';
		$this->assertTrue($this->agent->isReferral());
		$this->assertEquals('http://codeigniter.com/user_guide/', $this->agent->getReferrer());

		$this->setPrivateProperty($this->agent, 'referrer', null);
		unset($_SERVER['HTTP_REFERER']);
		$this->assertFalse($this->agent->isReferral());
		$this->assertEquals('', $this->agent->getReferrer());
	}

	// --------------------------------------------------------------------

	public function testAgentString()
	{
		$this->assertEquals($this->_user_agent, $this->agent->getAgentString());
	}

	// --------------------------------------------------------------------

	public function testBrowserInfo()
	{
		$this->assertEquals('Mac OS X', $this->agent->getPlatform());
		$this->assertEquals('Safari', $this->agent->getBrowser());
		$this->assertEquals('533.20.27', $this->agent->getVersion());
		$this->assertEquals('', $this->agent->getRobot());
	}

	// --------------------------------------------------------------------

	public function testParse()
	{
		$new_agent = 'Mozilla/5.0 (Android; Mobile; rv:13.0) Gecko/13.0 Firefox/13.0';
		$this->agent->parse($new_agent);

		$this->assertEquals('Android', $this->agent->getPlatform());
		$this->assertEquals('Firefox', $this->agent->getBrowser());
		$this->assertEquals('13.0', $this->agent->getVersion());
		$this->assertEquals('', $this->agent->getRobot());
		$this->assertEquals('Android', $this->agent->getMobile());
		$this->assertEquals($new_agent, $this->agent->getAgentString());
		$this->assertTrue($this->agent->isBrowser());
		$this->assertFalse($this->agent->isRobot());
		$this->assertTrue($this->agent->isMobile());
		$this->assertTrue($this->agent->isMobile('android'));
	}

	public function testParseBot()
	{
		$new_agent = 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)';
		$this->agent->parse($new_agent);

		$this->assertFalse($this->agent->isBrowser());
		$this->assertTrue($this->agent->isRobot());
		$this->assertFalse($this->agent->isRobot('Bob'));
		$this->assertFalse($this->agent->isMobile());
	}

}
