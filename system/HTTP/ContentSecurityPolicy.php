<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\HTTP;

/**
 * Class ContentSecurityPolicy
 *
 * Provides tools for working with the Content-Security-Policy header
 * to help defeat XSS attacks.
 *
 * @see     http://www.w3.org/TR/CSP/
 * @see     http://www.html5rocks.com/en/tutorials/security/content-security-policy/
 * @see     http://content-security-policy.com/
 * @see     https://www.owasp.org/index.php/Content_Security_Policy
 * @package CodeIgniter\HTTP
 */
class ContentSecurityPolicy
{

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
	 * @var string
	 */
	protected $reportURI;

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected $sandbox = [];

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
	 * @var boolean
	 */
	protected $upgradeInsecureRequests = false;

	/**
	 * Used for security enforcement
	 *
	 * @var boolean
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

	//--------------------------------------------------------------------

	/**
	 * ContentSecurityPolicy constructor.
	 *
	 * Stores our default values from the Config file.
	 *
	 * @param \Config\ContentSecurityPolicy $config
	 */
	public function __construct(\Config\ContentSecurityPolicy $config)
	{
		foreach ($config as $setting => $value) // @phpstan-ignore-line
		{
			if (isset($this->{$setting}))
			{
				$this->{$setting} = $value;
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Compiles and sets the appropriate headers in the request.
	 *
	 * Should be called just prior to sending the response to the user agent.
	 *
	 * @param ResponseInterface $response
	 */
	public function finalize(ResponseInterface &$response)
	{
		$this->generateNonces($response);

		$this->buildHeaders($response);
	}

	//--------------------------------------------------------------------

	/**
	 * If TRUE, nothing will be restricted. Instead all violations will
	 * be reported to the reportURI for monitoring. This is useful when
	 * you are just starting to implement the policy, and will help
	 * determine what errors need to be addressed before you turn on
	 * all filtering.
	 *
	 * @param boolean|true $value
	 *
	 * @return $this
	 */
	public function reportOnly(bool $value = true)
	{
		$this->reportOnly = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new base_uri value. Can be either a URI class or a simple string.
	 *
	 * base_uri restricts the URLs that can appear in a page’s <base> element.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-base-uri
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addBaseURI($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'baseURI', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

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
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addChildSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'childSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 *
	 * connect-src limits the origins to which you can connect
	 * (via XHR, WebSockets, and EventSource).
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-connect-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addConnectSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'connectSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 *
	 * default_src is the URI that is used for many of the settings when
	 * no other source has been set.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-default-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function setDefaultSrc($uri, bool $explicitReporting = null)
	{
		$this->defaultSrc = [(string) $uri => $explicitReporting ?? $this->reportOnly];

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 *
	 * font-src specifies the origins that can serve web fonts.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-font-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addFontSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'fontSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-form-action
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addFormAction($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'formAction', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new resource that should allow embedding the resource using
	 * <frame>, <iframe>, <object>, <embed>, or <applet>
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-frame-ancestors
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addFrameAncestor($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'frameAncestors', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for valid image sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-img-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addImageSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'imageSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for valid video and audio. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-media-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addMediaSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'mediaSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for manifest sources. Can be either
	 * a URI class or simple string.
	 *
	 * @see https://www.w3.org/TR/CSP/#directive-manifest-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addManifestSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'manifestSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for Flash and other plugin sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-object-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addObjectSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'objectSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Limits the types of plugins that can be used. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-plugin-types
	 *
	 * @param string|array $mime              One or more plugin mime types, separate by spaces
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addPluginType($mime, bool $explicitReporting = null)
	{
		$this->addOption($mime, 'pluginTypes', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Specifies a URL where a browser will send reports when a content
	 * security policy is violated. Can be either a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-report-uri
	 *
	 * @param string $uri
	 *
	 * @return $this
	 */
	public function setReportURI(string $uri)
	{
		$this->reportURI = $uri;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * specifies an HTML sandbox policy that the user agent applies to
	 * the protected resource.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-sandbox
	 *
	 * @param string|array $flags             An array of sandbox flags that can be added to the directive.
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addSandbox($flags, bool $explicitReporting = null)
	{
		$this->addOption($flags, 'sandbox', $explicitReporting ?? $this->reportOnly);
		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for javascript file sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-connect-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addScriptSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'scriptSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for CSS file sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-connect-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addStyleSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'styleSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets whether the user agents should rewrite URL schemes, changing
	 * HTTP to HTTPS.
	 *
	 * @param boolean $value
	 *
	 * @return $this
	 */
	public function upgradeInsecureRequests(bool $value = true)
	{
		$this->upgradeInsecureRequests = $value;

		return $this;
	}

	//--------------------------------------------------------------------
	// Utility
	//--------------------------------------------------------------------

	/**
	 * DRY method to add an string or array to a class property.
	 *
	 * @param string|array $options
	 * @param string       $target
	 * @param boolean|null $explicitReporting
	 */
	protected function addOption($options, string $target, bool $explicitReporting = null)
	{
		// Ensure we have an array to work with...
		if (is_string($this->{$target}))
		{
			$this->{$target} = [$this->{$target}];
		}

		if (is_array($options))
		{
			foreach ($options as $opt)
			{
				$this->{$target}[$opt] = $explicitReporting ?? $this->reportOnly;
			}
		}
		else
		{
			$this->{$target}[$options] = $explicitReporting ?? $this->reportOnly;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Scans the body of the request message and replaces any nonce
	 * placeholders with actual nonces, that we'll then add to our
	 * headers.
	 *
	 * @param ResponseInterface|\CodeIgniter\HTTP\Response $response
	 */
	protected function generateNonces(ResponseInterface &$response)
	{
		$body = $response->getBody();

		if (empty($body))
		{
			return;
		}

		if (! is_array($this->styleSrc))
		{
			$this->styleSrc = [$this->styleSrc];
		}
		if (! is_array($this->scriptSrc))
		{
			$this->scriptSrc = [$this->scriptSrc];
		}

		// Replace style placeholders with nonces
		$body = preg_replace_callback(
				'/{csp-style-nonce}/', function ($matches) {
					$nonce = bin2hex(random_bytes(12));

					$this->styleSrc[] = 'nonce-' . $nonce;

					return "nonce=\"{$nonce}\"";
				}, $body
		);

		// Replace script placeholders with nonces
		$body = preg_replace_callback(
				'/{csp-script-nonce}/', function ($matches) {
					$nonce = bin2hex(random_bytes(12));

					$this->scriptSrc[] = 'nonce-' . $nonce;

					return "nonce=\"{$nonce}\"";
				}, $body
		);

		$response->setBody($body);
	}

	//--------------------------------------------------------------------

	/**
	 * Based on the current state of the elements, will add the appropriate
	 * Content-Security-Policy and Content-Security-Policy-Report-Only headers
	 * with their values to the response object.
	 *
	 * @param ResponseInterface|\CodeIgniter\HTTP\Response $response
	 */
	protected function buildHeaders(ResponseInterface &$response)
	{
		// Ensure both headers are available and arrays...
		$response->setHeader('Content-Security-Policy', []);
		$response->setHeader('Content-Security-Policy-Report-Only', []);

		$directives = [
			'base-uri'        => 'baseURI',
			'child-src'       => 'childSrc',
			'connect-src'     => 'connectSrc',
			'default-src'     => 'defaultSrc',
			'font-src'        => 'fontSrc',
			'form-action'     => 'formAction',
			'frame-ancestors' => 'frameAncestors',
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

		// inject default base & default URIs if needed
		if (empty($this->baseURI))
		{
			$this->baseURI = 'self';
		}
		if (empty($this->defaultSrc))
		{
			$this->defaultSrc = 'self';
		}

		foreach ($directives as $name => $property)
		{
			// base_uri
			if (! empty($this->{$property}))
			{
				$this->addToHeader($name, $this->{$property});
			}
		}

		// Compile our own header strings here since if we just
		// append it to the response, it will be joined with
		// commas, not semi-colons as we need.
		if (! empty($this->tempHeaders))
		{
			$header = '';
			foreach ($this->tempHeaders as $name => $value)
			{
				$header .= " {$name} {$value};";
			}
			// add token only if needed
			if ($this->upgradeInsecureRequests)
			{
				$header .= ' upgrade-insecure-requests;';
			}

			$response->appendHeader('Content-Security-Policy', $header);
		}

		if (! empty($this->reportOnlyHeaders))
		{
			$header = '';
			foreach ($this->reportOnlyHeaders as $name => $value)
			{
				$header .= " {$name} {$value};";
			}
			$response->appendHeader('Content-Security-Policy-Report-Only', $header);
		}

		$this->tempHeaders       = [];
		$this->reportOnlyHeaders = [];
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a directive and it's options to the appropriate header. The $values
	 * array might have options that are geared toward either the regular or the
	 * reportOnly header, since it's viable to have both simultaneously.
	 *
	 * @param string            $name
	 * @param array|string|null $values
	 */
	protected function addToHeader(string $name, $values = null)
	{
		if (is_string($values))
		{
			$values = [$values => 0];
		}

		$sources       = [];
		$reportSources = [];

		foreach ($values as $value => $reportOnly)
		{
			if (is_numeric($value) && is_string($reportOnly) && ! empty($reportOnly))
			{
				$value      = $reportOnly;
				$reportOnly = 0;
			}

			if ($reportOnly === true)
			{
				$reportSources[] = in_array($value, $this->validSources, true) ? "'{$value}'" : $value;
			}
			else
			{
				if (strpos($value, 'nonce-') === 0)
				{
					$sources[] = "'{$value}'";
				}
				else
				{
					$sources[] = in_array($value, $this->validSources, true) ? "'{$value}'" : $value;
				}
			}
		}

		if (! empty($sources))
		{
			$this->tempHeaders[$name] = implode(' ', $sources);
		}

		if (! empty($reportSources))
		{
			$this->reportOnlyHeaders[$name] = implode(' ', $reportSources);
		}
	}

	//--------------------------------------------------------------------
}
