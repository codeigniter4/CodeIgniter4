<?php

$response->setLastModified(date('D, d M Y H:i:s'));
$response->setLastModified(DateTime::createFromFormat('!U', $timestamp));
