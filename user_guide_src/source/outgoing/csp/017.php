<?php

// get the CSP instance
$csp = $this->response->getCSP();

// Disable CSP for the request
$csp->disable();

// Re-enable CSP for the request
$csp->enable();
