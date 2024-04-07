<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use Config\App;
use Config\ContentSecurityPolicy as ContentSecurityPolicyConfig;

/**
 * Provides tools for working with the Content-Security-Policy header
 * to help defeat XSS attacks.
 *
 * @see http://www.w3.org/TR/CSP/
 * @see http://www.html5rocks.com/en/tutorials/security/content-security-policy/
 * @see http://content-security-policy.com/
 * @see https://www.owasp.org/index.php/Content_Security_Policy
 * @see \CodeIgniter\HTTP\ContentSecurityPolicyTest
 */
class ContentSecurityPolicy
{
    /**
     * CSP directives
     *
     * @var array<string, string>
     */
    protected array $directives = [
        'base-uri'        => 'baseURI',
        'child-src'       => 'childSrc',
        'connect-src'     => 'connectSrc',
        'default-src'     => 'defaultSrc',
        'font-src'        => 'fontSrc',
        'form-action'     => 'formAction',
        'frame-ancestors' => 'frameAncestors',
        'frame-src'       => 'frameSrc',
        'img-src'         => 'imageSrc',
        'media-src'       => 'mediaSrc',
        'object-src'      => 'objectSrc',
        'plugin-types'    => 'pluginTypes',
        'script-src'      => 'scriptSrc',
        'style-src'       => 'styleSrc',
        'manifest-src'    => 'manifestSrc',
        'sandbox'         => 'sandbox',
        'report-uri'      => 'reportURI',
    ];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $baseURI = [];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $childSrc = [];

    /**
     * Used for security enforcement
     *
     * @var array
     */
    protected $connectSrc = [];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $defaultSrc = [];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $fontSrc = [];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $formAction = [];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $frameAncestors = [];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $frameSrc = [];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $imageSrc = [];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $mediaSrc = [];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $objectSrc = [];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $pluginTypes = [];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $scriptSrc = [];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $styleSrc = [];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $manifestSrc = [];

    /**
     * Used for security enforcement
     *
     * @var array|string
     */
    protected $sandbox = [];

    /**
     * Used for security enforcement
     *
     * @var string|null
     */
    protected $reportURI;

    /**
     * Used for security enforcement
     *
     * @var bool
     */
    protected $upgradeInsecureRequests = false;

    /**
     * Used for security enforcement
     *
     * @var bool
     */
    protected $reportOnly = false;

    /**
     * Used for security enforcement
     *
     * @var array
     */
    protected $validSources = [
        'self',
        'none',
        'unsafe-inline',
        'unsafe-eval',
    ];

    /**
     * Used for security enforcement
     *
     * @var array
     */
    protected $nonces = [];

    /**
     * Nonce for style
     *
     * @var string
     */
    protected $styleNonce;

    /**
     * Nonce for script
     *
     * @var string
     */
    protected $scriptNonce;

    /**
     * Nonce tag for style
     *
     * @var string
     */
    protected $styleNonceTag = '{csp-style-nonce}';

    /**
     * Nonce tag for script
     *
     * @var string
     */
    protected $scriptNonceTag = '{csp-script-nonce}';

    /**
     * Replace nonce tag automatically
     *
     * @var bool
     */
    protected $autoNonce = true;

    /**
     * An array of header info since we have
     * to build ourself before passing to Response.
     *
     * @var array
     */
    protected $tempHeaders = [];

    /**
     * An array of header info to build
     * that should only be reported.
     *
     * @var array
     */
    protected $reportOnlyHeaders = [];

    /**
     * Whether Content Security Policy is being enforced.
     *
     * @var bool
     */
    protected $CSPEnabled = false;

    /**
     * Constructor.
     *
     * Stores our default values from the Config file.
     */
    public function __construct(ContentSecurityPolicyConfig $config)
    {
        $appConfig        = config(App::class);
        $this->CSPEnabled = $appConfig->CSPEnabled;

        foreach (get_object_vars($config) as $setting => $value) {
            if (property_exists($this, $setting)) {
                $this->{$setting} = $value;
            }
        }

        if (! is_array($this->styleSrc)) {
            $this->styleSrc = [$this->styleSrc];
        }

        if (! is_array($this->scriptSrc)) {
            $this->scriptSrc = [$this->scriptSrc];
        }
    }

    /**
     * Whether Content Security Policy is being enforced.
     */
    public function enabled(): bool
    {
        return $this->CSPEnabled;
    }

    /**
     * Get the nonce for the style tag.
     */
    public function getStyleNonce(): string
    {
        if ($this->styleNonce === null) {
            $this->styleNonce = bin2hex(random_bytes(12));
            $this->styleSrc[] = 'nonce-' . $this->styleNonce;
        }

        return $this->styleNonce;
    }

    /**
     * Get the nonce for the script tag.
     */
    public function getScriptNonce(): string
    {
        if ($this->scriptNonce === null) {
            $this->scriptNonce = bin2hex(random_bytes(12));
            $this->scriptSrc[] = 'nonce-' . $this->scriptNonce;
        }

        return $this->scriptNonce;
    }

    /**
     * Compiles and sets the appropriate headers in the request.
     *
     * Should be called just prior to sending the response to the user agent.
     *
     * @return void
     */
    public function finalize(ResponseInterface $response)
    {
        if ($this->autoNonce) {
            $this->generateNonces($response);
        }

        $this->buildHeaders($response);
    }

    /**
     * If TRUE, nothing will be restricted. Instead all violations will
     * be reported to the reportURI for monitoring. This is useful when
     * you are just starting to implement the policy, and will help
     * determine what errors need to be addressed before you turn on
     * all filtering.
     *
     * @return $this
     */
    public function reportOnly(bool $value = true)
    {
        $this->reportOnly = $value;

        return $this;
    }

    /**
     * Adds a new base_uri value. Can be either a URI class or a simple string.
     *
     * base_uri restricts the URLs that can appear in a page's <base> element.
     *
     * @see http://www.w3.org/TR/CSP/#directive-base-uri
     *
     * @param array|string $uri
     *
     * @return $this
     */
    public function addBaseURI($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'baseURI', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new valid endpoint for a form's action. Can be either
     * a URI class or a simple string.
     *
     * child-src lists the URLs for workers and embedded frame contents.
     * For example: child-src https://youtube.com would enable embedding
     * videos from YouTube but not from other origins.
     *
     * @see http://www.w3.org/TR/CSP/#directive-child-src
     *
     * @param array|string $uri
     *
     * @return $this
     */
    public function addChildSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'childSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new valid endpoint for a form's action. Can be either
     * a URI class or a simple string.
     *
     * connect-src limits the origins to which you can connect
     * (via XHR, WebSockets, and EventSource).
     *
     * @see http://www.w3.org/TR/CSP/#directive-connect-src
     *
     * @param array|string $uri
     *
     * @return $this
     */
    public function addConnectSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'connectSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new valid endpoint for a form's action. Can be either
     * a URI class or a simple string.
     *
     * default_src is the URI that is used for many of the settings when
     * no other source has been set.
     *
     * @see http://www.w3.org/TR/CSP/#directive-default-src
     *
     * @param array|string $uri
     *
     * @return $this
     */
    public function setDefaultSrc($uri, ?bool $explicitReporting = null)
    {
        $this->defaultSrc = [(string) $uri => $explicitReporting ?? $this->reportOnly];

        return $this;
    }

    /**
     * Adds a new valid endpoint for a form's action. Can be either
     * a URI class or a simple string.
     *
     * font-src specifies the origins that can serve web fonts.
     *
     * @see http://www.w3.org/TR/CSP/#directive-font-src
     *
     * @param array|string $uri
     *
     * @return $this
     */
    public function addFontSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'fontSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new valid endpoint for a form's action. Can be either
     * a URI class or a simple string.
     *
     * @see http://www.w3.org/TR/CSP/#directive-form-action
     *
     * @param array|string $uri
     *
     * @return $this
     */
    public function addFormAction($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'formAction', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new resource that should allow embedding the resource using
     * <frame>, <iframe>, <object>, <embed>, or <applet>
     *
     * @see http://www.w3.org/TR/CSP/#directive-frame-ancestors
     *
     * @param array|string $uri
     *
     * @return $this
     */
    public function addFrameAncestor($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'frameAncestors', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new valid endpoint for valid frame sources. Can be either
     * a URI class or a simple string.
     *
     * @see http://www.w3.org/TR/CSP/#directive-frame-src
     *
     * @param array|string $uri
     *
     * @return $this
     */
    public function addFrameSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'frameSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new valid endpoint for valid image sources. Can be either
     * a URI class or a simple string.
     *
     * @see http://www.w3.org/TR/CSP/#directive-img-src
     *
     * @param array|string $uri
     *
     * @return $this
     */
    public function addImageSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'imageSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new valid endpoint for valid video and audio. Can be either
     * a URI class or a simple string.
     *
     * @see http://www.w3.org/TR/CSP/#directive-media-src
     *
     * @param array|string $uri
     *
     * @return $this
     */
    public function addMediaSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'mediaSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new valid endpoint for manifest sources. Can be either
     * a URI class or simple string.
     *
     * @see https://www.w3.org/TR/CSP/#directive-manifest-src
     *
     * @param array|string $uri
     *
     * @return $this
     */
    public function addManifestSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'manifestSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new valid endpoint for Flash and other plugin sources. Can be either
     * a URI class or a simple string.
     *
     * @see http://www.w3.org/TR/CSP/#directive-object-src
     *
     * @param array|string $uri
     *
     * @return $this
     */
    public function addObjectSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'objectSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Limits the types of plugins that can be used. Can be either
     * a URI class or a simple string.
     *
     * @see http://www.w3.org/TR/CSP/#directive-plugin-types
     *
     * @param array|string $mime One or more plugin mime types, separate by spaces
     *
     * @return $this
     */
    public function addPluginType($mime, ?bool $explicitReporting = null)
    {
        $this->addOption($mime, 'pluginTypes', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Specifies a URL where a browser will send reports when a content
     * security policy is violated. Can be either a URI class or a simple string.
     *
     * @see http://www.w3.org/TR/CSP/#directive-report-uri
     *
     * @return $this
     */
    public function setReportURI(string $uri)
    {
        $this->reportURI = $uri;

        return $this;
    }

    /**
     * specifies an HTML sandbox policy that the user agent applies to
     * the protected resource.
     *
     * @see http://www.w3.org/TR/CSP/#directive-sandbox
     *
     * @param array|string $flags An array of sandbox flags that can be added to the directive.
     *
     * @return $this
     */
    public function addSandbox($flags, ?bool $explicitReporting = null)
    {
        $this->addOption($flags, 'sandbox', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new valid endpoint for javascript file sources. Can be either
     * a URI class or a simple string.
     *
     * @see http://www.w3.org/TR/CSP/#directive-connect-src
     *
     * @param array|string $uri
     *
     * @return $this
     */
    public function addScriptSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'scriptSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new valid endpoint for CSS file sources. Can be either
     * a URI class or a simple string.
     *
     * @see http://www.w3.org/TR/CSP/#directive-connect-src
     *
     * @param array|string $uri
     *
     * @return $this
     */
    public function addStyleSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'styleSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Sets whether the user agents should rewrite URL schemes, changing
     * HTTP to HTTPS.
     *
     * @return $this
     */
    public function upgradeInsecureRequests(bool $value = true)
    {
        $this->upgradeInsecureRequests = $value;

        return $this;
    }

    /**
     * DRY method to add an string or array to a class property.
     *
     * @param list<string>|string $options
     *
     * @return void
     */
    protected function addOption($options, string $target, ?bool $explicitReporting = null)
    {
        // Ensure we have an array to work with...
        if (is_string($this->{$target})) {
            $this->{$target} = [$this->{$target}];
        }

        if (is_array($options)) {
            foreach ($options as $opt) {
                $this->{$target}[$opt] = $explicitReporting ?? $this->reportOnly;
            }
        } else {
            $this->{$target}[$options] = $explicitReporting ?? $this->reportOnly;
        }
    }

    /**
     * Scans the body of the request message and replaces any nonce
     * placeholders with actual nonces, that we'll then add to our
     * headers.
     *
     * @return void
     */
    protected function generateNonces(ResponseInterface $response)
    {
        $body = $response->getBody();

        if (empty($body)) {
            return;
        }

        // Replace style and script placeholders with nonces
        $pattern = '/(' . preg_quote($this->styleNonceTag, '/')
            . '|' . preg_quote($this->scriptNonceTag, '/') . ')/';

        $body = preg_replace_callback($pattern, function ($match) {
            $nonce = $match[0] === $this->styleNonceTag ? $this->getStyleNonce() : $this->getScriptNonce();

            return "nonce=\"{$nonce}\"";
        }, $body);

        $response->setBody($body);
    }

    /**
     * Based on the current state of the elements, will add the appropriate
     * Content-Security-Policy and Content-Security-Policy-Report-Only headers
     * with their values to the response object.
     *
     * @return void
     */
    protected function buildHeaders(ResponseInterface $response)
    {
        // Ensure both headers are available and arrays...
        $response->setHeader('Content-Security-Policy', []);
        $response->setHeader('Content-Security-Policy-Report-Only', []);

        // inject default base & default URIs if needed
        if (empty($this->baseURI)) {
            $this->baseURI = 'self';
        }

        if (empty($this->defaultSrc)) {
            $this->defaultSrc = 'self';
        }

        foreach ($this->directives as $name => $property) {
            if (! empty($this->{$property})) {
                $this->addToHeader($name, $this->{$property});
            }
        }

        // Compile our own header strings here since if we just
        // append it to the response, it will be joined with
        // commas, not semi-colons as we need.
        if (! empty($this->tempHeaders)) {
            $header = '';

            foreach ($this->tempHeaders as $name => $value) {
                $header .= " {$name} {$value};";
            }

            // add token only if needed
            if ($this->upgradeInsecureRequests) {
                $header .= ' upgrade-insecure-requests;';
            }

            $response->appendHeader('Content-Security-Policy', $header);
        }

        if (! empty($this->reportOnlyHeaders)) {
            $header = '';

            foreach ($this->reportOnlyHeaders as $name => $value) {
                $header .= " {$name} {$value};";
            }

            $response->appendHeader('Content-Security-Policy-Report-Only', $header);
        }

        $this->tempHeaders       = [];
        $this->reportOnlyHeaders = [];
    }

    /**
     * Adds a directive and it's options to the appropriate header. The $values
     * array might have options that are geared toward either the regular or the
     * reportOnly header, since it's viable to have both simultaneously.
     *
     * @param array|string|null $values
     *
     * @return void
     */
    protected function addToHeader(string $name, $values = null)
    {
        if (is_string($values)) {
            $values = [$values => $this->reportOnly];
        }

        $sources       = [];
        $reportSources = [];

        foreach ($values as $value => $reportOnly) {
            if (is_numeric($value) && is_string($reportOnly) && ($reportOnly !== '')) {
                $value      = $reportOnly;
                $reportOnly = $this->reportOnly;
            }

            if (str_starts_with($value, 'nonce-')) {
                $value = "'{$value}'";
            }

            if ($reportOnly === true) {
                $reportSources[] = in_array($value, $this->validSources, true) ? "'{$value}'" : $value;
            } else {
                $sources[] = in_array($value, $this->validSources, true) ? "'{$value}'" : $value;
            }
        }

        if ($sources !== []) {
            $this->tempHeaders[$name] = implode(' ', $sources);
        }

        if ($reportSources !== []) {
            $this->reportOnlyHeaders[$name] = implode(' ', $reportSources);
        }
    }

    /**
     * Clear the directive.
     *
     * @param string $directive CSP directive
     */
    public function clearDirective(string $directive): void
    {
        if ($directive === 'report-uris') {
            $this->{$this->directives[$directive]} = null;

            return;
        }

        $this->{$this->directives[$directive]} = [];
    }
}
