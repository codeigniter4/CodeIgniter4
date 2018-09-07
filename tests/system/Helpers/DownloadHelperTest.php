<?php

namespace CodeIgniter\Helpers;


final class DownloadHelperTest extends \CIUnitTestCase
{

	public function setUp()
	{
		parent::setUp();
		helper('download');
	}

	public function testForceDownload()
	{
		$this->markTestSkipped('Cant easily test');
	}

}
