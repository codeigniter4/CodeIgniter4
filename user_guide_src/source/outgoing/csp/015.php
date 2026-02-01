<?php

// get the CSP instance
$csp = $this->response->getCSP();

$csp->setReportURI('https://example.com/csp-reports');

// Starting in v4.7.0, you can use the setReportToEndpoint() method
// to set the reporting endpoint for CSP reports
$csp->addReportingEndpoints([
    'default' => 'https://example.com/csp-reports',
    'reports' => 'https://example.com/other-csp-reports',
]);
$csp->setReportToEndpoint('default');
