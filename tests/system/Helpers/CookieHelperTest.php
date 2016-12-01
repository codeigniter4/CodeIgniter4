<?php namespace CodeIgniter\HTTP;

use Config\App;
use CodeIgniter\Services;

final class cookieHelperTest extends \CIUnitTestCase
{

    private $name;
    private $value;
    private $expire;
    private $response;

    public function setUp()
    {        
        $this->name   = 'greetings';
        $this->value  = 'hello world';
        $this->expire = 9999;

        Services::injectMock('response', new MockResponse(new App()));
        $this->response = service('response');
        
        helper('cookie');
    }

    //--------------------------------------------------------------------
    
    public function testSetCookie()
    {
        $this->response->setCookie($this->name, $this->value, $this->expire);
        
        //TODO: Find a way for set_cookie() to use the MockResponse object.
        //set_cookie($this->name, $this->value, $this->expire);

        $this->assertTrue($this->response->hasCookie($this->name));

        $this->response->deleteCookie($this->name);
    }

    //--------------------------------------------------------------------

    public function testSetCookieByArrayParameters()
    {
        $cookieAttr = array
        (
            'name'   => $this->name, 
            'value'  => $this->value, 
            'expire' => $this->expire
        );
        //set_cookie($cookieAttr);
        $this->response->setCookie($cookieAttr);
        
        $this->assertEquals(get_cookie($this->name), $this->value);

        $this->response->deleteCookie($this->name);
    }

    //--------------------------------------------------------------------

    public function testGetCookie()
    {
        $pre  = 'Hello, I try to';
        $pst  = 'your site';
        $unsec = "$pre <script>alert('Hack');</script> $pst";
        $sec   = "$pre [removed]alert&#40;&#39;Hack&#39;&#41;;[removed] $pst";
        $unsecured = 'unsecured';
        $secured   = 'secured';

        //set_cookie($unsecured, $unsec, $this->expire);
        //set_cookie($secured,   $sec,   $this->expire);
        $this->response->setCookie($unsecured, $unsec, $this->expire);
        $this->response->setCookie($secured, $sec, $this->expire);
        
        $this->assertEquals($unsec, get_cookie($unsecured, false));
        $this->assertEquals($sec,   get_cookie($secured,   true));

        $this->response->deleteCookie($unsecured);
        $this->response->deleteCookie($secured);   
    }

    //--------------------------------------------------------------------

    public function testDeleteCookie()
    {
        //set_cookie($this->name, $this->value, $this->expire);
        $this->response->setCookie($this->name, $this->value, $this->expire);
        
        $this->response->deleteCookie($this->name);
        
        //$this->assertEquals(get_cookie($this->name), '');
        $this->assertTrue($this->response->hasCookie($this->name));
    }

}