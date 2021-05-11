<?php

use Cache\IntegrationTests\SimpleCacheTest as TestCase;
use CodeIgniter\I18n\Time;
use CodeIgniter\Psr\Cache\SimpleCache;
use Config\Services;

class SimpleCacheTest extends TestCase
{
	public function createSimpleCache()
	{
		Services::resetSingle('cache');
		Time::setTestNow(null);
		return new SimpleCache();
	}
}
