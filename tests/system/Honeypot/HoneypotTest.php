<?php namespace CodeIgniter\Honeypot;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Honeypot\Honeypot;
use CodeIgniter\Config\Services;

class HoneypotTest extends CIUnitTestCase
{

    protected $request;
    protected $response;
    protected $honeypot;
    
    public function setUp()
    {
        parent::setUp();
        $this->request = Services::request();
        $this->response = Services::response();    
        $config = new \Config\Honeypot();
        $this->honeypot = new Honeypot($config);    
        
    }

    public function testAttachHoneypot()
    {     

        $this->response->setBody('<form></form>');
        $this->honeypot->attachHoneypot($this->response);      
        $this->assertContains('honeypot', $this->response->getBody());
        $this->response->setBody('<div></div>');
        $this->assertNotContains('honeypot', $this->response->getBody());
    }

    public function testHasHoneypot()
    {
            
        $_REQUEST['honeypot'] = 'hey';
        $this->assertEquals(true, $this->honeypot->hasContent($this->request));
        $_POST['honeypot'] = 'hey';
        $this->assertEquals(true, $this->honeypot->hasContent($this->request));
        $_GET['honeypot'] = 'hey';
        $this->assertEquals(true, $this->honeypot->hasContent($this->request));
    }
}