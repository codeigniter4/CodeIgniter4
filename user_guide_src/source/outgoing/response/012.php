<?php

// specify the default directive treatment
$this->response->CSP->reportOnly(false);

// specify the origin to use if none provided for a directive
$this->response->CSP->setDefaultSrc('cdn.example.com');

// specify the URL that "report-only" reports get sent to
$this->response->CSP->setReportURI('http://example.com/csp/reports');

// specify that HTTP requests be upgraded to HTTPS
$this->response->CSP->upgradeInsecureRequests(true);

// add types or origins to CSP directives
// assuming that the default treatment is to block rather than just report
$this->response->CSP->addBaseURI('example.com', true); // report only
$this->response->CSP->addChildSrc('https://youtube.com'); // blocked
$this->response->CSP->addConnectSrc('https://*.facebook.com', false); // blocked
$this->response->CSP->addFontSrc('fonts.example.com');
$this->response->CSP->addFormAction('self');
$this->response->CSP->addFrameAncestor('none', true); // report this one
$this->response->CSP->addImageSrc('cdn.example.com');
$this->response->CSP->addMediaSrc('cdn.example.com');
$this->response->CSP->addManifestSrc('cdn.example.com');
$this->response->CSP->addObjectSrc('cdn.example.com', false); // reject from here
$this->response->CSP->addPluginType('application/pdf', false); // reject this media type
$this->response->CSP->addScriptSrc('scripts.example.com', true); // allow but report requests from here
$this->response->CSP->addStyleSrc('css.example.com');
$this->response->CSP->addSandbox(['allow-forms', 'allow-scripts']);
