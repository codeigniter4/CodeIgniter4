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

use CodeIgniter\Exceptions\InvalidArgumentException;
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
    private const DIRECTIVES_ALLOWING_SOURCE_LISTS = [
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
        'sandbox'         => 'sandbox',
        'manifest-src'    => 'manifestSrc',
        'script-src-elem' => 'scriptSrcElem',
        'script-src-attr' => 'scriptSrcAttr',
        'style-src-elem'  => 'styleSrcElem',
        'style-src-attr'  => 'styleSrcAttr',
        'worker-src'      => 'workerSrc',
    ];

    /**
     * Map of CSP directives to this class's properties.
     *
     * @var array<string, string>
     */
    protected array $directives = [
        ...self::DIRECTIVES_ALLOWING_SOURCE_LISTS,
        'report-uri' => 'reportURI',
        'report-to'  => 'reportTo',
    ];

    /**
     * The `base-uri` directive restricts the URLs that can be used to specify the document base URL.
     *
     * @var array<string, bool>|string|null
     */
    protected $baseURI = [];

    /**
     * The `child-src` directive governs the creation of nested browsing contexts as well
     * as Worker execution contexts.
     *
     * @var array<string, bool>|string
     */
    protected $childSrc = [];

    /**
     * The `connect-src` directive restricts which URLs the protected resource can load using script interfaces.
     *
     * @var array<string, bool>|string
     */
    protected $connectSrc = [];

    /**
     * The `default-src` directive sets a default source list for a number of directives.
     *
     * @var array<string, bool>|string|null
     */
    protected $defaultSrc = [];

    /**
     * The `font-src` directive restricts from where the protected resource can load fonts.
     *
     * @var array<string, bool>|string
     */
    protected $fontSrc = [];

    /**
     * The `form-action` directive restricts which URLs can be used as the action of HTML form elements.
     *
     * @var array<string, bool>|string
     */
    protected $formAction = [];

    /**
     * The `frame-ancestors` directive indicates whether the user agent should allow embedding
     * the resource using a `frame`, `iframe`, `object`, `embed` or `applet` element,
     * or equivalent functionality in non-HTML resources.
     *
     * @var array<string, bool>|string
     */
    protected $frameAncestors = [];

    /**
     * The `frame-src` directive restricts the URLs which may be loaded into child navigables.
     *
     * @var array<string, bool>|string
     */
    protected $frameSrc = [];

    /**
     * The `img-src` directive restricts from where the protected resource can load images.
     *
     * @var array<string, bool>|string
     */
    protected $imageSrc = [];

    /**
     * The `media-src` directive restricts from where the protected resource can load video,
     * audio, and associated text tracks.
     *
     * @var array<string, bool>|string
     */
    protected $mediaSrc = [];

    /**
     * The `object-src` directive restricts from where the protected resource can load plugins.
     *
     * @var array<string, bool>|string
     */
    protected $objectSrc = [];

    /**
     * The `plugin-types` directive restricts the set of plugins that can be invoked by the
     * protected resource by limiting the types of resources that can be embedded.
     *
     * @var array<string, bool>|string
     */
    protected $pluginTypes = [];

    /**
     * The `script-src` directive restricts which scripts the protected resource can execute.
     *
     * @var array<string, bool>|string
     */
    protected $scriptSrc = [];

    /**
     * The `style-src` directive restricts which styles the user may applies to the protected resource.
     *
     * @var array<string, bool>|string
     */
    protected $styleSrc = [];

    /**
     * The `sandbox` directive specifies an HTML sandbox policy that the user agent applies to the protected resource.
     *
     * @var array<string, bool>|string
     */
    protected $sandbox = [];

    /**
     * The `report-uri` directive specifies a URL to which the user agent sends reports about policy violation.
     *
     * @var string|null
     */
    protected $reportURI;

    /**
     * The `report-to` directive specifies a named group in a Reporting API
     * endpoint to which the user agent sends reports about policy violation.
     */
    protected ?string $reportTo = null;

    // --------------------------------------------------------------
    // CSP Level 3 Directives
    // --------------------------------------------------------------

    /**
     * The `manifest-src` directive restricts the URLs from which application manifests may be loaded.
     *
     * @var array<string, bool>|string
     */
    protected $manifestSrc = [];

    /**
     * The `script-src-elem` directive applies to all script requests and script blocks.
     *
     * @var array<string, bool>|string
     */
    protected array|string $scriptSrcElem = [];

    /**
     * The `script-src-attr` directive applies to event handlers and, if present,
     * it will override the `script-src` directive for relevant checks.
     *
     * @var array<string, bool>|string
     */
    protected array|string $scriptSrcAttr = [];

    /**
     * The `style-src-elem` directive governs the behaviour of styles except
     * for styles defined in inline attributes.
     *
     * @var array<string, bool>|string
     */
    protected array|string $styleSrcElem = [];

    /**
     * The `style-src-attr` directive governs the behaviour of style attributes.
     *
     * @var array<string, bool>|string
     */
    protected array|string $styleSrcAttr = [];

    /**
     * The `worker-src` directive restricts the URLs which may be loaded as a `Worker`,
     * `SharedWorker`, or `ServiceWorker`.
     *
     * @var array<string, bool>|string
     */
    protected array|string $workerSrc = [];

    /**
     * Instructs user agents to rewrite URL schemes by changing HTTP to HTTPS.
     *
     * @var bool
     */
    protected $upgradeInsecureRequests = false;

    /**
     * Set to `true` to make all directives report-only instead of enforced.
     *
     * @var bool
     */
    protected $reportOnly = false;

    /**
     * Set of valid keyword-sources.
     *
     * @see https://www.w3.org/TR/CSP3/#source-expression
     *
     * @var list<string>
     */
    protected $validSources = [
        // CSP2 keywords
        'self',
        'none',
        'unsafe-inline',
        'unsafe-eval',
        // CSP3 keywords
        'strict-dynamic',
        'unsafe-hashes',
        'report-sample',
        'unsafe-allow-redirects',
        'wasm-unsafe-eval',
        'trusted-types-eval',
        'report-sha256',
        'report-sha384',
        'report-sha512',
    ];

    /**
     * Set of nonces generated.
     *
     * @var list<string>
     *
     * @deprecated 4.7.0 Never used.
     */
    protected $nonces = [];

    /**
     * Nonce for style tags.
     *
     * @var string|null
     */
    protected $styleNonce;

    /**
     * Nonce for script tags.
     *
     * @var string|null
     */
    protected $scriptNonce;

    /**
     * Nonce placeholder for style tags.
     *
     * @var string
     */
    protected $styleNonceTag = '{csp-style-nonce}';

    /**
     * Nonce placeholder for script tags.
     *
     * @var string
     */
    protected $scriptNonceTag = '{csp-script-nonce}';

    /**
     * Replace nonce tags automatically?
     *
     * @var bool
     */
    protected $autoNonce = true;

    /**
     * An array of header info since we have to build
     * ourselves before passing to a Response object.
     *
     * @var array<string, string>
     */
    protected $tempHeaders = [];

    /**
     * An array of header info to build that should only be reported.
     *
     * @var array<string, string>
     */
    protected $reportOnlyHeaders = [];

    /**
     * Whether Content Security Policy is being enforced.
     *
     * @var bool
     */
    protected $CSPEnabled = false;

    /**
     * Map of reporting endpoints to their URLs.
     *
     * @var array<string, string>
     */
    private array $reportingEndpoints = [];

    /**
     * Stores our default values from the Config file.
     */
    public function __construct(ContentSecurityPolicyConfig $config)
    {
        $this->CSPEnabled = config(App::class)->CSPEnabled;

        foreach (get_object_vars($config) as $setting => $value) {
            if (! property_exists($this, $setting)) {
                continue;
            }

            if (
                in_array($setting, self::DIRECTIVES_ALLOWING_SOURCE_LISTS, true)
                && is_array($value)
                && array_is_list($value)
            ) {
                // Config sets these directives as `list<string>|string`
                // but we need them as `array<string, bool>` internally.
                $this->{$setting} = array_combine($value, array_fill(0, count($value), $this->reportOnly));

                continue;
            }

            $this->{$setting} = $value;
        }

        if (! is_array($this->styleSrc)) {
            $this->styleSrc = [$this->styleSrc => $this->reportOnly];
        }

        if (! is_array($this->scriptSrc)) {
            $this->scriptSrc = [$this->scriptSrc => $this->reportOnly];
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
            $this->styleNonce = base64_encode(random_bytes(12));
            $this->addStyleSrc('nonce-' . $this->styleNonce);
        }

        return $this->styleNonce;
    }

    /**
     * Get the nonce for the script tag.
     */
    public function getScriptNonce(): string
    {
        if ($this->scriptNonce === null) {
            $this->scriptNonce = base64_encode(random_bytes(12));
            $this->addScriptSrc('nonce-' . $this->scriptNonce);
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
     * Adds a new value to the `base-uri` directive.
     *
     * `base-uri` restricts the URLs that can appear in a page's <base> element.
     *
     * @see http://www.w3.org/TR/CSP/#directive-base-uri
     *
     * @param list<string>|string $uri
     *
     * @return $this
     */
    public function addBaseURI($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'baseURI', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `child-src` directive.
     *
     * `child-src` lists the URLs for workers and embedded frame contents.
     * For example: child-src https://youtube.com would enable embedding
     * videos from YouTube but not from other origins.
     *
     * @see http://www.w3.org/TR/CSP/#directive-child-src
     *
     * @param list<string>|string $uri
     *
     * @return $this
     */
    public function addChildSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'childSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `connect-src` directive.
     *
     * `connect-src` limits the origins to which you can connect
     * (via XHR, WebSockets, and EventSource).
     *
     * @see http://www.w3.org/TR/CSP/#directive-connect-src
     *
     * @param list<string>|string $uri
     *
     * @return $this
     */
    public function addConnectSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'connectSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `default-src` directive.
     *
     * `default-src` is the URI that is used for many of the settings when
     * no other source has been set.
     *
     * @see http://www.w3.org/TR/CSP/#directive-default-src
     *
     * @param list<string>|string $uri
     *
     * @return $this
     */
    public function setDefaultSrc($uri, ?bool $explicitReporting = null)
    {
        $this->defaultSrc = [(string) $uri => $explicitReporting ?? $this->reportOnly];

        return $this;
    }

    /**
     * Adds a new value to the `font-src` directive.
     *
     * `font-src` specifies the origins that can serve web fonts.
     *
     * @see http://www.w3.org/TR/CSP/#directive-font-src
     *
     * @param list<string>|string $uri
     *
     * @return $this
     */
    public function addFontSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'fontSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `form-action` directive.
     *
     * @see http://www.w3.org/TR/CSP/#directive-form-action
     *
     * @param list<string>|string $uri
     *
     * @return $this
     */
    public function addFormAction($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'formAction', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `frame-ancestors` directive.
     *
     * @see http://www.w3.org/TR/CSP/#directive-frame-ancestors
     *
     * @param list<string>|string $uri
     *
     * @return $this
     */
    public function addFrameAncestor($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'frameAncestors', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `frame-src` directive.
     *
     * @see http://www.w3.org/TR/CSP/#directive-frame-src
     *
     * @param list<string>|string $uri
     *
     * @return $this
     */
    public function addFrameSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'frameSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `img-src` directive.
     *
     * @see http://www.w3.org/TR/CSP/#directive-img-src
     *
     * @param list<string>|string $uri
     *
     * @return $this
     */
    public function addImageSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'imageSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `media-src` directive.
     *
     * @see http://www.w3.org/TR/CSP/#directive-media-src
     *
     * @param list<string>|string $uri
     *
     * @return $this
     */
    public function addMediaSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'mediaSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `manifest-src` directive.
     *
     * @see https://www.w3.org/TR/CSP/#directive-manifest-src
     *
     * @param list<string>|string $uri
     *
     * @return $this
     */
    public function addManifestSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'manifestSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `object-src` directive.
     *
     * @see http://www.w3.org/TR/CSP/#directive-object-src
     *
     * @param list<string>|string $uri
     *
     * @return $this
     */
    public function addObjectSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'objectSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `plugin-types` directive.
     *
     * @see http://www.w3.org/TR/CSP/#directive-plugin-types
     *
     * @param list<string>|string $mime
     *
     * @return $this
     */
    public function addPluginType($mime, ?bool $explicitReporting = null)
    {
        $this->addOption($mime, 'pluginTypes', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `sandbox` directive.
     *
     * @see http://www.w3.org/TR/CSP/#directive-sandbox
     *
     * @param list<string>|string $flags
     *
     * @return $this
     */
    public function addSandbox($flags, ?bool $explicitReporting = null)
    {
        $this->addOption($flags, 'sandbox', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `script-src` directive.
     *
     * @see http://www.w3.org/TR/CSP/#directive-script-src
     *
     * @param list<string>|string $uri
     *
     * @return $this
     */
    public function addScriptSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'scriptSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `script-src-elem` directive.
     *
     * @see https://www.w3.org/TR/CSP/#directive-script-src-elem
     *
     * @param list<string>|string $uri
     */
    public function addScriptSrcElem(array|string $uri, ?bool $explicitReporting = null): static
    {
        $this->addOption($uri, 'scriptSrcElem', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `script-src-attr` directive.
     *
     * @see https://www.w3.org/TR/CSP/#directive-script-src-attr
     *
     * @param list<string>|string $uri
     */
    public function addScriptSrcAttr(array|string $uri, ?bool $explicitReporting = null): static
    {
        $this->addOption($uri, 'scriptSrcAttr', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `style-src` directive.
     *
     * @see http://www.w3.org/TR/CSP/#directive-style-src
     *
     * @param list<string>|string $uri
     *
     * @return $this
     */
    public function addStyleSrc($uri, ?bool $explicitReporting = null)
    {
        $this->addOption($uri, 'styleSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `style-src-elem` directive.
     *
     * @see https://www.w3.org/TR/CSP/#directive-style-src-elem
     *
     * @param list<string>|string $uri
     */
    public function addStyleSrcElem(array|string $uri, ?bool $explicitReporting = null): static
    {
        $this->addOption($uri, 'styleSrcElem', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `style-src-attr` directive.
     *
     * @see https://www.w3.org/TR/CSP/#directive-style-src-attr
     *
     * @param list<string>|string $uri
     */
    public function addStyleSrcAttr(array|string $uri, ?bool $explicitReporting = null): static
    {
        $this->addOption($uri, 'styleSrcAttr', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Adds a new value to the `worker-src` directive.
     *
     * @see https://www.w3.org/TR/CSP/#directive-worker-src
     *
     * @param list<string>|string $uri
     */
    public function addWorkerSrc($uri, ?bool $explicitReporting = null): static
    {
        $this->addOption($uri, 'workerSrc', $explicitReporting ?? $this->reportOnly);

        return $this;
    }

    /**
     * Sets whether the user agents should rewrite URL schemes, changing HTTP to HTTPS.
     *
     * @return $this
     */
    public function upgradeInsecureRequests(bool $value = true)
    {
        $this->upgradeInsecureRequests = $value;

        return $this;
    }

    /**
     * Specifies a URL where a browser will send reports when a content
     * security policy is violated.
     *
     * @see http://www.w3.org/TR/CSP/#directive-report-uri
     *
     * @param string $uri URL to send reports. Set `''` if you want to remove
     *                    this directive at runtime.
     *
     * @return $this
     */
    public function setReportURI(string $uri)
    {
        $this->reportURI = $uri;

        return $this;
    }

    /**
     * Specifies a named group in a Reporting API endpoint to which the user
     * agent sends reports about policy violation.
     *
     * @see https://www.w3.org/TR/CSP/#directive-report-to
     *
     * @param string $endpoint The name of the reporting endpoint. Set `''` if you
     *                         want to remove this directive at runtime.
     */
    public function setReportToEndpoint(string $endpoint): static
    {
        if ($endpoint === '') {
            $this->reportURI = null;
            $this->reportTo  = null;

            return $this;
        }

        if (! array_key_exists($endpoint, $this->reportingEndpoints)) {
            throw new InvalidArgumentException(sprintf('The reporting endpoint "%s" has not been defined.', $endpoint));
        }

        $this->reportURI = $this->reportingEndpoints[$endpoint]; // for BC with browsers that do not support `report-to`
        $this->reportTo  = $endpoint;

        return $this;
    }

    /**
     * Adds reporting endpoints to the `Reporting-Endpoints` header.
     *
     * @param array<string, string> $endpoint
     */
    public function addReportingEndpoints(array $endpoint): static
    {
        foreach ($endpoint as $name => $url) {
            $this->reportingEndpoints[$name] = $url;
        }

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
            $this->{$target} = [$this->{$target} => $this->reportOnly];
        }

        $options = is_array($options) ? $options : [$options];

        foreach ($options as $option) {
            $this->{$target}[$option] = $explicitReporting ?? $this->reportOnly;
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
        $body = (string) $response->getBody();

        if ($body === '') {
            return;
        }

        // Replace style and script placeholders with nonces
        $pattern = sprintf('/(%s|%s)/', preg_quote($this->styleNonceTag, '/'), preg_quote($this->scriptNonceTag, '/'));

        $body = preg_replace_callback($pattern, function ($match): string {
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
        $response->setHeader('Content-Security-Policy', []);
        $response->setHeader('Content-Security-Policy-Report-Only', []);
        $response->setHeader('Reporting-Endpoints', []);

        if (in_array($this->baseURI, ['', null, []], true)) {
            $this->baseURI = 'self';
        }

        if (in_array($this->defaultSrc, ['', null, []], true)) {
            $this->defaultSrc = 'self';
        }

        foreach ($this->directives as $name => $property) {
            if ($name === 'report-uri' && (string) $this->reportURI === '') {
                continue;
            }

            if ($name === 'report-to' && (string) $this->reportTo === '') {
                continue;
            }

            if ($this->{$property} !== null) {
                $this->addToHeader($name, $this->{$property});
            }
        }

        // Compile our own header strings here since if we just
        // append it to the response, it will be joined with
        // commas, not semi-colons as we need.
        if ($this->reportingEndpoints !== []) {
            $endpoints = [];

            foreach ($this->reportingEndpoints as $name => $url) {
                $endpoints[] = trim("{$name}=\"{$url}\"");
            }

            $response->appendHeader('Reporting-Endpoints', implode(', ', $endpoints));
            $this->reportingEndpoints = [];
        }

        if ($this->tempHeaders !== []) {
            $header = [];

            foreach ($this->tempHeaders as $name => $value) {
                $header[] = trim("{$name} {$value}");
            }

            if ($this->upgradeInsecureRequests) {
                $header[] = 'upgrade-insecure-requests';
            }

            $response->appendHeader('Content-Security-Policy', implode('; ', $header));
            $this->tempHeaders = [];
        }

        if ($this->reportOnlyHeaders !== []) {
            $header = [];

            foreach ($this->reportOnlyHeaders as $name => $value) {
                $header[] = trim("{$name} {$value}");
            }

            $response->appendHeader('Content-Security-Policy-Report-Only', implode('; ', $header));
            $this->reportOnlyHeaders = [];
        }
    }

    /**
     * Adds a directive and its options to the appropriate header. The $values
     * array might have options that are geared toward either the regular or the
     * reportOnly header, since it's viable to have both simultaneously.
     *
     * @param array<string, bool>|string $values
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
            if (
                in_array($value, $this->validSources, true)
                || str_starts_with($value, 'nonce-')
                || str_starts_with($value, 'sha256-')
                || str_starts_with($value, 'sha384-')
                || str_starts_with($value, 'sha512-')
            ) {
                $value = "'{$value}'";
            }

            if ($reportOnly) {
                $reportSources[] = $value;
            } else {
                $sources[] = $value;
            }
        }

        if ($sources !== []) {
            $this->tempHeaders[$name] = implode(' ', $sources);
        }

        if ($reportSources !== []) {
            $this->reportOnlyHeaders[$name] = implode(' ', $reportSources);
        }
    }

    public function clearDirective(string $directive): void
    {
        if (! array_key_exists($directive, $this->directives)) {
            return;
        }

        if ($directive === 'report-uri') {
            $this->reportURI = null;

            return;
        }

        if ($directive === 'report-to') {
            $this->reportURI = null;
            $this->reportTo  = null;

            return;
        }

        $this->{$this->directives[$directive]} = [];
    }
}
