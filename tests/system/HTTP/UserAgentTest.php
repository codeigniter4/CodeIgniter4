<?php

namespace CodeIgniter\HTTP;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class UserAgentTest extends CIUnitTestCase
{
    protected $_user_agent = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; en-us) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27';
    protected $_mobile_ua  = 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_1 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8B117 Safari/6531.22.7';

    /**
     * @var UserAgent
     */
    protected $agent;

    protected function setUp(): void
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
        $this->assertFalse($this->agent->isMobile());
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
        $this->assertSame('http://codeigniter.com/user_guide/', $this->agent->getReferrer());

        $this->setPrivateProperty($this->agent, 'referrer', null);
        unset($_SERVER['HTTP_REFERER']);
        $this->assertFalse($this->agent->isReferral());
        $this->assertSame('', $this->agent->getReferrer());
    }

    // --------------------------------------------------------------------

    public function testAgentString()
    {
        $this->assertSame($this->_user_agent, $this->agent->getAgentString());
    }

    // --------------------------------------------------------------------

    public function testBrowserInfo()
    {
        $this->assertSame('Mac OS X', $this->agent->getPlatform());
        $this->assertSame('Safari', $this->agent->getBrowser());
        $this->assertSame('533.20.27', $this->agent->getVersion());
        $this->assertSame('', $this->agent->getRobot());
    }

    // --------------------------------------------------------------------

    public function testParse()
    {
        $newAgent = 'Mozilla/5.0 (Android; Mobile; rv:13.0) Gecko/13.0 Firefox/13.0';
        $this->agent->parse($newAgent);

        $this->assertSame('Android', $this->agent->getPlatform());
        $this->assertSame('Firefox', $this->agent->getBrowser());
        $this->assertSame('13.0', $this->agent->getVersion());
        $this->assertSame('', $this->agent->getRobot());
        $this->assertSame('Android', $this->agent->getMobile());
        $this->assertSame($newAgent, $this->agent->getAgentString());
        $this->assertTrue($this->agent->isBrowser());
        $this->assertFalse($this->agent->isRobot());
        $this->assertTrue($this->agent->isMobile());
        $this->assertTrue($this->agent->isMobile('android'));
    }

    public function testParseBot()
    {
        $newAgent = 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)';
        $this->agent->parse($newAgent);

        $this->assertFalse($this->agent->isBrowser());
        $this->assertTrue($this->agent->isRobot());
        $this->assertFalse($this->agent->isRobot('Bob'));
        $this->assertFalse($this->agent->isMobile());
    }

    public function testEmptyUserAgentVariable()
    {
        unset($_SERVER['HTTP_USER_AGENT']);
        $agent = new UserAgent();
        $this->assertEmpty((string) $agent);
    }
}
