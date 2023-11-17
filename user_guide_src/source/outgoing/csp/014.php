<?php

// get the CSP instance
$csp = $this->response->getCSP();

$csp->clearDirective('style-src');
