<?php

$url = $result->getRedirectUrl();
$this->assertEquals(site_url('foo/bar'), $url);
