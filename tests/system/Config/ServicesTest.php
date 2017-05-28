<?php namespace Config;

class ServicesTest extends \CIUnitTestCase
{
	protected $config;

	public function setUp()
	{
		Services::reset();
		$config = new App();
	}

	// Test the cUrl get method, we suppose to test other verse: put, delete, update  
	public function testCurlRequestGetMethod()
	{
		$client = Services::curlrequest([
            'debug' => true,
            'follow_redirects' => true
        ]);

        $response = $client->request('GET', 'https://jsonplaceholder.typicode.com/posts/1');

        $this->assertContains('sunt aut facere repellat provident occaecati excepturi optio reprehenderit', $response->getBody());
	}


	public function testNewExceptions()
	{
		$actual = Services::exceptions($this->config);
		$this->assertInstanceOf(\CodeIgniter\Debug\Exceptions::class, $actual);
	}

	public function testNewIterator()
	{
		$actual = Services::iterator();
		$this->assertInstanceOf(\CodeIgniter\Debug\Iterator::class, $actual);
	}

	public function testNewNegotiatorWithNullConfig()
	{
		$actual = Services::negotiator(null);
		$this->assertInstanceOf(\CodeIgniter\HTTP\Negotiate::class, $actual);
	}

	public function testNewClirequestWithNullConfig()
	{
		$actual = Services::clirequest(null);
		$this->assertInstanceOf(\CodeIgniter\HTTP\CLIRequest::class, $actual);
	}

	public function testNewPager()
	{
		$actual = Services::pager(null);
		$this->assertInstanceOf(\CodeIgniter\Pager\Pager::class, $actual);
	}

	public function testNewThrottler()
	{
		$actual = Services::throttler(null);
		$this->assertInstanceOf(\CodeIgniter\Throttle\Throttler::class, $actual);
	}

	public function testNewToolbar()
	{
		$actual = Services::toolbar(null);
		$this->assertInstanceOf(\CodeIgniter\Debug\Toolbar::class, $actual);
	}

	public function testNewUri()
	{
		$actual = Services::uri(null);
		$this->assertInstanceOf(\CodeIgniter\HTTP\URI::class, $actual);
	}

	public function testNewValidation()
	{
		$actual = Services::validation(null);
		$this->assertInstanceOf(\CodeIgniter\Validation\Validation::class, $actual);
	}

	public function testNewViewcell()
	{
		$actual = Services::viewcell(null);
		$this->assertInstanceOf(\CodeIgniter\View\Cell::class, $actual);
	}

}
