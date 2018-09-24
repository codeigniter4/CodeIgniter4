<?php namespace CodeIgniter\HTTP;

class DownloadResponseTest extends \CIUnitTestCase
{
	public function testCanGetStatusCode()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$this->assertSame(200, $response->getStatusCode());
	}
}
