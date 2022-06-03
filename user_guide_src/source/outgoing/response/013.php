<?php

$response->addChildSrc('https://youtube.com'); // allowed
$response->reportOnly(true);
$response->addChildSrc('https://metube.com'); // allowed but reported
$response->addChildSrc('https://ourtube.com', false); // allowed
