<?php

// get the CSP instance
$csp = $this->response->getCSP();

// specify the default directive treatment
$csp->reportOnly(false);

// specify the origin to use if none provided for a directive
$csp->setDefaultSrc('cdn.example.com');

// specify the URL that "report-only" reports get sent to
$csp->setReportURI('http://example.com/csp/reports');

// specify that HTTP requests be upgraded to HTTPS
$csp->upgradeInsecureRequests(true);

// add types or origins to CSP directives
// assuming that the default treatment is to block rather than just report
$csp->addBaseURI('example.com', true); // report only
$csp->addChildSrc('https://youtube.com'); // blocked
$csp->addConnectSrc('https://*.facebook.com', false); // blocked
$csp->addFontSrc('fonts.example.com');
$csp->addFormAction('self');
$csp->addFrameAncestor('none', true); // report this one
$csp->addImageSrc('cdn.example.com');
$csp->addMediaSrc('cdn.example.com');
$csp->addManifestSrc('cdn.example.com');
$csp->addObjectSrc('cdn.example.com', false); // reject from here
$csp->addPluginType('application/pdf', false); // reject this media type
$csp->addScriptSrc('scripts.example.com', true); // allow but report requests from here
$csp->addStyleSrc('css.example.com');
$csp->addSandbox(['allow-forms', 'allow-scripts']);
