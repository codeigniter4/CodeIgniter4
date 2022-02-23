<?php

// specify the default directive treatment
$response->CSP->reportOnly(false);

// specify the origin to use if none provided for a directive
$response->CSP->setDefaultSrc('cdn.example.com');

// specify the URL that "report-only" reports get sent to
$response->CSP->setReportURI('http://example.com/csp/reports');

// specify that HTTP requests be upgraded to HTTPS
$response->CSP->upgradeInsecureRequests(true);

// add types or origins to CSP directives
// assuming that the default treatment is to block rather than just report
$response->CSP->addBaseURI('example.com', true); // report only
$response->CSP->addChildSrc('https://youtube.com'); // blocked
$response->CSP->addConnectSrc('https://*.facebook.com', false); // blocked
$response->CSP->addFontSrc('fonts.example.com');
$response->CSP->addFormAction('self');
$response->CSP->addFrameAncestor('none', true); // report this one
$response->CSP->addImageSrc('cdn.example.com');
$response->CSP->addMediaSrc('cdn.example.com');
$response->CSP->addManifestSrc('cdn.example.com');
$response->CSP->addObjectSrc('cdn.example.com', false); // reject from here
$response->CSP->addPluginType('application/pdf', false); // reject this media type
$response->CSP->addScriptSrc('scripts.example.com', true); // allow but report requests from here
$response->CSP->addStyleSrc('css.example.com');
$response->CSP->addSandbox(['allow-forms', 'allow-scripts']);
