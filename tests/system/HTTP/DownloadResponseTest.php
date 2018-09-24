<?php namespace CodeIgniter\HTTP;

class DownloadResponseTest extends \CIUnitTestCase
{
	public function testCanGetStatusCode()
	{
		$response = new DownloadResponse();

		$this->assertSame(200, $response->getStatusCode());
	}
}
