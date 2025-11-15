<?php

$mock = mock(\CodeIgniter\Cache\CacheFactory::class);

// Assert that a cached item named $key exists
$mock->assertHas($key);
// Assert that a cached item named $key exists with a value of $value
$mock->assertHasValue($key, $value);
// Assert that a cached item named $key does NOT exist
$mock->assertMissing($key);
