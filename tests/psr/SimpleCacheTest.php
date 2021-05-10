<?php

use Cache\IntegrationTests\SimpleCacheTest as TestCase;
use CodeIgniter\Psr\Cache\SimpleCache;

class SimpleCacheTest extends TestCase
{
	public function createSimpleCache()
	{
		return new SimpleCache();
	}
}
