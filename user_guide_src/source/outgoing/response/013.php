<?php

$this->response->CSP->addChildSrc('https://youtube.com'); // allowed
$this->response->CSP->reportOnly(true);
$this->response->CSP->addChildSrc('https://metube.com'); // allowed but reported
$this->response->CSP->addChildSrc('https://ourtube.com', false); // allowed
