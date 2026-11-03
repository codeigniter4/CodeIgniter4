<?php

$mock = mock(\CodeIgniter\Cache\CacheFactory::class);
// Never cache any items during this test.
$mock->bypass();
