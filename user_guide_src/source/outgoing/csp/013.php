<?php

// get the CSP instance
$csp = $this->response->getCSP();

$csp->addChildSrc('https://youtube.com'); // allowed
$csp->reportOnly(true);
$csp->addChildSrc('https://metube.com'); // allowed but reported
$csp->addChildSrc('https://ourtube.com', false); // allowed
