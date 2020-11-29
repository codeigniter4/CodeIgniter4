<?php

namespace Http\Psr7Test\Tests;

use CodeIgniter\HTTP\URI;
use Http\Psr7Test\UriIntegrationTest;

class UriTest extends UriIntegrationTest
{
	public function createUri($uri)
	{
		return new URI($uri);
	}
}
