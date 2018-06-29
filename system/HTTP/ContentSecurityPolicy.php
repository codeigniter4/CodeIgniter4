<?php namespace CodeIgniter\HTTP;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

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
	 * @var array
	 */
	protected $baseURI = [];

	/**
	 * Used for security enforcement
	 * @var array
	 */
	protected $childSrc = [];

	/**
	 * Used for security enforcement
	 * @var array
	 */
	protected $connectSrc = [];

	/**
	 * Used for security enforcement
	 * @var array
	 */
	protected $defaultSrc = [];

	/**
	 * Used for security enforcement
	 * @var array
	 */
	protected $fontSrc = [];

	/**
	 * Used for security enforcement
	 * @var array
	 */
	protected $formAction = [];

	/**
	 * Used for security enforcement
	 * @var type
	 */
	protected $frameAncestors = null;

	/**
	 * Used for security enforcement
	 * @var array
	 */
	protected $imageSrc = [];

	/**
	 * Used for security enforcement
	 * @var array
	 */
	protected $mediaSrc = [];

	/**
	 * Used for security enforcement
	 * @var array
	 */
	protected $objectSrc = [];

	/**
	 * Used for security enforcement
	 * @var type
	 */
	protected $pluginTypes = null;

	/**
	 * Used for security enforcement
	 * @var string
	 */
	protected $reportURI = null;

	/**
	 * Used for security enforcement
	 * @var bool
	 */
	protected $sandbox = false;

	/**
	 * Used for security enforcement
	 * @var array
	 */
	protected $scriptSrc = [];

	/**
	 * Used for security enforcement
	 * @var array
	 */
	protected $styleSrc = [];
	
	/**
	 * Used for security enforcement
	 * @var array
	 */
	protected $manifestSrc = [];

	/**
	 * Used for security enforcement
	 * @var bool
	 */
	protected $upgradeInsecureRequests = false;

	/**
	 * Used for security enforcement
	 * @var bool
	 */
	protected $reportOnly = false;

	/**
	 * Used for security enforcement
	 * @var array
	 */
	protected $validSources = ['self', 'none', 'unsafe-inline', 'unsafe-eval'];

	/**
	 * Used for security enforcement
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
		foreach ($config as $setting => $value)
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
	//--------------------------------------------------------------------
	// Setters
	//--------------------------------------------------------------------

	/**
	 * If TRUE, nothing will be restricted. Instead all violations will
	 * be reported to the reportURI for monitoring. This is useful when
	 * you are just starting to implement the policy, and will help
	 * determine what errors need to be addressed before you turn on
	 * all filtering.
	 *
	 * @param bool|true $value
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
	 * Sets the base_uri value. Can be either a URI class or a simple string.
	 *
	 * base_uri restricts the URLs that can appear in a page’s <base> element.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-base-uri
	 *
	 * @param string $uri
	 * @param bool $reportOnly
	 *
	 * @return $this
	 */
	public function setBaseURI($uri, bool $reportOnly)
	{
		$this->baseURI = [(string) $uri => $reportOnly];

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
	 * @param      $uri
	 * @param bool $reportOnly
	 *
	 * @return $this
	 */
	public function addChildSrc($uri, bool $reportOnly = false)
	{
		$this->addOption($uri, 'childSrc', $reportOnly);

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
	 * @param      $uri
	 * @param bool $reportOnly
	 *
	 * @return $this
	 */
	public function addConnectSrc($uri, bool $reportOnly = false)
	{
		$this->addOption($uri, 'connectSrc', $reportOnly);

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
	 * @param      $uri
	 * @param bool $reportOnly
	 *
	 * @return $this
	 */
	public function setDefaultSrc($uri, bool $reportOnly = false)
	{
		$this->defaultSrc = [(string) $uri => $reportOnly];

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
	 * @param      $uri
	 * @param bool $reportOnly
	 *
	 * @return $this
	 */
	public function addFontSrc($uri, bool $reportOnly = false)
	{
		$this->addOption($uri, 'fontSrc', $reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-form-action
	 *
	 * @param      $uri
	 * @param bool $reportOnly
	 *
	 * @return $this
	 */
	public function addFormAction($uri, bool $reportOnly = false)
	{
		$this->addOption($uri, 'formAction', $reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new resource that should allow embedding the resource using
	 * <frame>, <iframe>, <object>, <embed>, or <applet>
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-frame-ancestors
	 *
	 * @param      $uri
	 * @param bool $reportOnly
	 *
	 * @return $this
	 */
	public function addFrameAncestor($uri, bool $reportOnly = false)
	{
		$this->addOption($uri, 'frameAncestors', $reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for valid image sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-img-src
	 *
	 * @param      $uri
	 * @param bool $reportOnly
	 *
	 * @return $this
	 */
	public function addImageSrc($uri, bool $reportOnly = false)
	{
		$this->addOption($uri, 'imageSrc', $reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for valid video and audio. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-media-src
	 *
	 * @param      $uri
	 * @param bool $reportOnly
	 *
	 * @return $this
	 */
	public function addMediaSrc($uri, bool $reportOnly = false)
	{
		$this->addOption($uri, 'mediaSrc', $reportOnly);

		return $this;
	}
	
	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for manifest sources. Can be either
	 * a URI class or simple string.
	 *
	 * @see https://www.w3.org/TR/CSP/#directive-manifest-src
	 *
	 * @param	   $uri
	 * @param bool $reportOnly
	 *
	 * @return $this
	 */
	public function addManifestSrc($uri, bool $reportOnly = false)
	{
		$this->addOption($uri, 'manifestSrc', $reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for Flash and other plugin sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-object-src
	 *
	 * @param      $uri
	 * @param bool $reportOnly
	 *
	 * @return $this
	 */
	public function addObjectSrc($uri, bool $reportOnly = false)
	{
		$this->addOption($uri, 'objectSrc', $reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Limits the types of plugins that can be used. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-plugin-types
	 *
	 * @param string $mime One or more plugin mime types, separate by spaces
	 * @param bool   $reportOnly
	 *
	 * @return $this
	 */
	public function addPluginType($mime, bool $reportOnly = false)
	{
		$this->addOption($mime, 'pluginTypes', $reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Specifies a URL where a browser will send reports when a content
	 * security policy is violated. Can be either a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-report-uri
	 *
	 * @param $uri
	 *
	 * @return $this
	 */
	public function setReportURI($uri)
	{
		$this->reportURI = (string) $uri;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * specifies an HTML sandbox policy that the user agent applies to
	 * the protected resource.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-sandbox
	 *
	 * @param bool  $value
	 * @param array $flags An array of sandbox flags that can be added to the directive.
	 *
	 * @return $this
	 */
	public function setSandbox(bool $value = true, array $flags = null)
	{
		if (empty($this->sandbox) && empty($flags))
		{
			$this->sandbox = $value;
		}
		else
		{
			$this->sandbox = $flags;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for javascript file sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-connect-src
	 *
	 * @param      $uri
	 * @param bool $reportOnly
	 *
	 * @return $this
	 */
	public function addScriptSrc($uri, bool $reportOnly = false)
	{
		$this->addOption($uri, 'scriptSrc', $reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for CSS file sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-connect-src
	 *
	 * @param      $uri
	 * @param bool $reportOnly
	 *
	 * @return $this
	 */
	public function addStyleSrc($uri, bool $reportOnly = false)
	{
		$this->addOption($uri, 'styleSrc', $reportOnly);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets whether the user agents should rewrite URL schemes, changing
	 * HTTP to HTTPS.
	 *
	 * @param bool|true $value
	 *
	 * @return $this
	 */
	public function upgradeInsecureRequests(bool $value = true)
	{
		$this->upgradeInsecureRequests = $value;

		return $this;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Utility
	//--------------------------------------------------------------------

	/**
	 * DRY method to add an string or array to a class property.
	 *
	 * @param        $options
	 * @param string $target
	 * @param bool   $reportOnly If TRUE, this item will be reported, not restricted
	 */
	protected function addOption($options, string $target, bool $reportOnly = false)
	{
		// Ensure we have an array to work with...
		if (is_string($this->{$target}))
		{
			$this->{$target} = [$this->{$target}];
		}

		if (is_array($options))
		{
			$newOptions = [];
			foreach ($options as $opt)
			{
				$newOptions[] = [$opt => $reportOnly];
			}

			$this->{$target} = array_merge($this->{$target}, $newOptions);
			unset($newOptions);
		}
		else
		{
			$this->{$target}[$options] = $reportOnly;
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
			return;

		if ( ! is_array($this->styleSrc))
			$this->styleSrc = [$this->styleSrc];
		if ( ! is_array($this->scriptSrc))
			$this->scriptSrc = [$this->scriptSrc];

		// Replace style placeholders with nonces
		$body = preg_replace_callback(
				'/{csp-style-nonce}/', function ($matches) {
			$nonce = bin2hex(random_bytes(12));

			$this->styleSrc[] = 'nonce-' . $nonce;

			return "nonce={$nonce}";
		}, $body
		);

		// Replace script placeholders with nonces
		$body = preg_replace_callback(
				'/{csp-script-nonce}/', function ($matches) {
			$nonce = bin2hex(random_bytes(12));

			$this->scriptSrc[] = 'nonce-' . $nonce;

			return "nonce={$nonce}";
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
			'base-uri'			 => 'baseURI',
			'child-src'			 => 'childSrc',
			'connect-src'		 => 'connectSrc',
			'default-src'		 => 'defaultSrc',
			'font-src'			 => 'fontSrc',
			'form-action'		 => 'formAction',
			'frame-ancestors'	 => 'frameAncestors',
			'img-src'			 => 'imageSrc',
			'media-src'			 => 'mediaSrc',
			'object-src'		 => 'objectSrc',
			'plugin-types'		 => 'pluginTypes',
			'script-src'		 => 'scriptSrc',
			'style-src'			 => 'styleSrc',
			'manifest-src'		 => 'manifestSrc',
			'sandbox'			 => 'sandbox',
			'report-uri'		 => 'reportURI'
		];

		foreach ($directives as $name => $property)
		{
			// base_uri
			if ( ! empty($this->{$property}))
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

		$this->tempHeaders = [];
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
		if (empty($values))
		{
			// It's possible that directives like 'sandbox' will not
			// have any values passed in, so add them to the main policy.
			$this->tempHeaders[$name] = null;
			return;
		}

		if (is_string($values))
		{
			$values = [$values => 0];
		}

		$sources = [];
		$reportSources = [];

		foreach ($values as $value => $reportOnly)
		{
			if (is_numeric($value) && is_string($reportOnly) && ! empty($reportOnly))
			{
				$value = $reportOnly;
				$reportOnly = 0;
			}

			if ($reportOnly === true)
			{
				$reportSources[] = in_array($value, $this->validSources) ? "'{$value}'" : $value;
			}
			else
			{
				if (strpos($value, 'nonce-') === 0)
				{
					$sources[] = "'{$value}'";
				}
				else
				{
					$sources[] = in_array($value, $this->validSources) ? "'{$value}'" : $value;
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
