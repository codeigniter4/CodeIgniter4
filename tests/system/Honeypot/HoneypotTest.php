<?php namespace CodeIgniter\Honeypot;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Honeypot\Honeypoter;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Config\Services;
use Config\App;

class HoneypotTest extends CIUnitTestCase
{

    protected $request;
    protected $response;
    
    public function setUp()
    {
        parent::setUp();
        $this->request = new IncomingRequest(new App(),
                new \CodeIgniter\HTTP\URI(),
                null,
                new \CodeIgniter\HTTP\UserAgent()
            );
        $this->response = Services::response();    
    }

    public function testAttachHoneypot()
    {     

        $this->response->setBody('<form></form>');
        Honeypoter::attachHoneypot($this->response);      
        $this->assertContains('honeypot',$this->response->getBody());
        $this->response->setBody('<div></div>');
        $this->assertNotContains('honeypot',$this->response->getBody());
    }

    public function testCheckHoneypot()
    {
            
        $_REQUEST['honeypot'] = 'hey';
        $this->assertEquals(true, Honeypoter::honeypotHasContent($this->request));
        $_POST['honeypot'] = 'hey';
        $this->assertEquals(true, Honeypoter::honeypotHasContent($this->request));
        $_GET['honeypot'] = 'hey';
        $this->assertEquals(true, Honeypoter::honeypotHasContent($this->request));
    }
}