<?php namespace CodeIgniter\API;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\MockIncomingRequest;
use CodeIgniter\HTTP\MockResponse;
use CodeIgniter\HTTP\URI;

class ResponseTraitTest extends \CIUnitTestCase
{
    protected $request;
    protected $response;


    protected function makeController(array $userConfig = [], string $uri = 'http://example.com', array $userHeaders = [])
    {
        $config = [
            'baseURL'          => 'http://example.com',
            'uriProtocol'      => 'REQUEST_URI',
            'defaultLocale'    => 'en',
            'negotiateLocale'  => false,
            'supportedLocales' => ['en'],
            'CSPEnabled'       => false,
            'cookiePrefix'     => '',
            'cookieDomain'     => '',
            'cookiePath'       => '/',
            'cookieSecure'     => false,
            'cookieHTTPOnly'   => false,
            'proxyIPs'         => []
        ];

        $config = array_merge($config, $userConfig);

        $this->request  = new MockIncomingRequest((object)$config, new URI($uri), null);
        $this->response = new MockResponse((object)$config);

        // Insert headers into request.
        $headers = [
            'Accept' => 'text/html'
        ];
        $headers = array_merge($headers, $userHeaders);

        foreach ($headers as $key => $value)
        {
            $this->request->setHeader($key, $value);
        }

        // Create the controller class finally.
        $controller = new class($this->request, $this->response)
        {
            use ResponseTrait;

            protected $request;
            protected $response;

            public function __construct($request, $response)
            {
                $this->request = $request;
                $this->response = $response;
            }

        };

        return $controller;
    }

    //--------------------------------------------------------------------

    public function testRespondSets404WithNoData()
    {
        $controller = $this->makeController();
        $controller->respond(null, null);

        $this->assertEquals(404, $this->response->getStatusCode());
        $this->assertNull($this->response->getBody());
    }

    //--------------------------------------------------------------------

    public function testRespondSetsStatusWithEmptyData()
    {
        $controller = $this->makeController();
        $controller->respond(null, 201);

        $this->assertEquals(201, $this->response->getStatusCode());
        $this->assertNull($this->response->getBody());
    }

    //--------------------------------------------------------------------

    public function testRespondSetsCorrectBodyAndStatus()
    {
        $controller = $this->makeController();
        $controller->respond('something', 201);

        $this->assertEquals(201, $this->response->getStatusCode());
        $this->assertEquals('something', $this->response->getBody());
        $this->assertTrue(strpos($this->response->getHeaderLine('Content-Type'), 'text/html') === 0);
    }

    //--------------------------------------------------------------------
}
