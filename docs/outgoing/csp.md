# Content Security Policy

## What is Content Security Policy?

One of the best protections you have against XSS attacks is to implement a Content Security Policy (CSP) on the site. This requires you to specify and authorize each source of content that is included in your site's HTML, including images, stylesheets, JavaScript files, and so on. The browser will reject content from sources that are not explicitly approved. This authorization is defined within the response's `Content-Security-Policy` header and offers various configuration options.

This sounds complex, and on some sites can definitely be challenging. For many simple sites, though, where all content is served by the same domain (e.g., **http://example.com**), it is very simple to integrate.

As this is a complex subject, this user guide will not go over all of the details. For more information, you should visit the following sites:

- [Content Security Policy main site](https://content-security-policy.com/)
- [W3C Specification](https://www.w3.org/TR/CSP)
- [Introduction at HTML5Rocks](https://www.html5rocks.com/en/tutorials/security/content-security-policy/)
- [Article at SitePoint](https://www.sitepoint.com/improving-web-security-with-the-content-security-policy/)

## Turning CSP On

!!! important "Important"
    The [Debug Toolbar](../testing/debugging.md#the-debug-toolbar) may use Kint, which outputs inline scripts. Therefore, when CSP is turned on, a CSP nonce is automatically output for the Debug Toolbar. However, if you are not using CSP nonces, this will change the CSP header to something you do not intend, and it will behave differently than in production; if you want to verify CSP behavior, turn off the Debug Toolbar.

By default, support for this is off. To enable support in your application, edit the `CSPEnabled` value in **app/Config/App.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    // ...

    public bool $CSPEnabled = true;
}
```

When enabled, the response object will contain an instance of `CodeIgniter\HTTP\ContentSecurityPolicy`. The values set in **app/Config/ContentSecurityPolicy.php** are applied to that instance, and if no changes are needed during runtime, then the correctly formatted header is sent and you're all done.

With CSP enabled, two header lines are added to the HTTP response: a **Content-Security-Policy** header, with policies identifying content types or origins that are explicitly allowed for different contexts, and a **Content-Security-Policy-Report-Only** header, which identifies content types or origins that will be allowed but which will also be reported to the destination of your choice.

Our implementation provides for a default treatment, changeable through the `reportOnly()` method. When an additional entry is added to a CSP directive, as shown below, it will be added to the CSP header appropriate for blocking or preventing. That can be overridden on a per-call basis, by providing an optional second parameter to the adding method call.

## Runtime Configuration

If your application needs to make changes at run-time, you can access the instance at `$this->response->getCSP()` in your controllers.

The class holds a number of methods that map pretty clearly to the appropriate header value that you need to set. Examples are shown below, with different combinations of parameters, though all accept either a directive name or an array of them:

```php
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

// the following CSP3 directives are available in v4.7.0 and later
$csp->addScriptSrcAttr('trusted.com');
$csp->addScriptSrcElem('trusted.com');
$csp->addStyleSrcAttr('trusted.com');
$csp->addStyleSrcElem('trusted.com');
$csp->addWorkerSrc('workers.example.com');
```

The first parameter to each of the "add" methods is an appropriate string value, or an array of them.

### Report Only

The `reportOnly()` method allows you to specify the default reporting treatment for subsequent sources, unless overridden.

For instance, you could specify that youtube.com was allowed, and then provide several allowed but reported sources:

```php
<?php

// get the CSP instance
$csp = $this->response->getCSP();

$csp->addChildSrc('https://youtube.com'); // allowed
$csp->reportOnly(true);
$csp->addChildSrc('https://metube.com'); // allowed but reported
$csp->addChildSrc('https://ourtube.com', false); // allowed
```

### Reporting Directives

To specify the URL you want the reports to be sent to, you can use the `setReportURI()` method.

!!! success "Available from version 4.7.0"

CSP Level 3 deprecates the `report-uri` directive in favor of `report-to`. Therefore, you can use the `setReportToEndpoint()` method to set the reporting endpoint for CSP reports. Before adding this directive, make sure the reporting endpoints are already defined using the `addReportingEndpoints()` method.

```php
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
```

For backward compatibility with browsers that do not support the `report-to` directive, CodeIgniter4 will also set the `report-uri` directive when you use the `setReportToEndpoint()` method.

### Clear Directives

If you want to clear existing CSP directives, you can use the `clearDirective()` method:

```php
<?php

// get the CSP instance
$csp = $this->response->getCSP();

$csp->clearDirective('style-src');
```

## Inline Content

Inline scripts and styles can be injected through user-generated content. To allow only trusted inline content, CSP lets you add a nonce to trusted `<style>` and `<script>` tags and include the same nonce in the response's Content-Security-Policy header.

### Using Placeholders

This is a pain to handle in real life, and is most secure when generated on the fly. To make this simple, you can include a `{csp-style-nonce}` or `{csp-script-nonce}` placeholder in the tag and it will be handled for you automatically:

```
// Original
<script {csp-script-nonce}>
    console.log("Script won't run as it doesn't contain a nonce attribute");
</script>

// Becomes
<script nonce="Eskdikejidojdk978Ad8jf">
    console.log("Script won't run as it doesn't contain a nonce attribute");
</script>

// OR
<style {csp-style-nonce}>
    . . .
</style>
```

!!! warning "Warning"
    If an attacker injects a string like `<script {csp-script-nonce}>`, it might become the real nonce attribute with this functionality. You can customize the placeholder string with the `$scriptNonceTag` and `$styleNonceTag` properties in **app/Config/ContentSecurityPolicy.php**.

### Using Functions

If you don't like the auto replacement functionality above, you can turn it off by setting `$autoNonce = false` in **app/Config/ContentSecurityPolicy.php**.

In this case, you can use the functions `csp_script_nonce()` and `csp_style_nonce()`:

```
// Original
<script <?= csp_script_nonce() ?>>
    console.log("Script won't run as it doesn't contain a nonce attribute");
</script>

// Becomes
<script nonce="Eskdikejidojdk978Ad8jf">
    console.log("Script won't run as it doesn't contain a nonce attribute");
</script>

// OR
<style <?= csp_style_nonce() ?>>
    . . .
</style>
```
