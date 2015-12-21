<?php namespace CodeIgniter\HTTP;

/**
 * Class ContentSecurityPolicy
 *
 * Provides tools for working with the Content-Security-Policy header
 * to help defeat XSS attacks.
 *
 * @see http://www.w3.org/TR/CSP/
 * @see http://www.html5rocks.com/en/tutorials/security/content-security-policy/
 * @package CodeIgniter\HTTP
 */
class ContentSecurityPolicy
{
	protected $base_uri = '';

	protected $childSrc = [];

	protected $connectSrc = [];

	protected $defaultSrc = [];

	protected $fontSrc = [];

	protected $formAction = [];

	protected $frameAncestors = null;

	protected $imageSrc = [];

	protected $mediaSrc = [];

	protected $objectSrc = [];

	protected $pluginTypes = null;

	protected $reportURI = null;

	protected $sandbox = false;

	protected $scriptSrc = [];

	protected $styleSrc = [];

	protected $upgradeInsecureRequests = false;

	protected $reportOnly = false;

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
	public function reportOnly(bool $value=true)
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
	 * @param $uri
	 *
	 * @return $this
	 */
	public function setBaseURI($uri)
	{
	    $this->base_uri = (string)$uri;

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
	 * @param $uri
	 *
	 * @return $this
	 */
	public function addChildSrc($uri)
	{
		$this->addOption($uri, 'childSrc');

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
	 * @param $uri
	 *
	 * @return $this
	 */
	public function addConnectSrc($uri)
	{
		$this->addOption($uri, 'connectSrc');

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
	 * @param $uri
	 *
	 * @return $this
	 */
	public function addDefaultSrc($uri)
	{
		$this->defaultSrc[] = (string)$uri;

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
	 * @param $uri
	 *
	 * @return $this
	 */
	public function addFontSrc($uri)
	{
		$this->addOption($uri, 'fontSrc');

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-form-action
	 * @param $uri
	 *
	 * @return $this
	 */
	public function addFormAction($uri)
	{
		$this->addOption($uri, 'formAction');
		
		return $this;
	}
	
	//--------------------------------------------------------------------

	/**
	 * Adds a new resource that should allow embedding the resource using
	 * <frame>, <iframe>, <object>, <embed>, or <applet>
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-frame-ancestors
	 * @param $uri
	 *
	 * @return $this
	 */
	public function addFrameAncestor($uri)
	{
		$this->addOption($uri, 'frameAncestors');

		return $this;
	}
	
	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for valid image sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-img-src
	 * @param $uri
	 *
	 * @return $this
	 */
	public function addImageSrc($uri)
	{
		$this->addOption($uri, 'imageSrc');

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for valid video and audio. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-media-src
	 * @param $uri
	 *
	 * @return $this
	 */
	public function addMediaSrc($uri)
	{
		$this->addOption($uri, 'mediaSrc');

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for Flash and other plugin sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-object-src
	 * @param $uri
	 *
	 * @return $this
	 */
	public function addObjectSrc($uri)
	{
		$this->addOption($uri, 'objectSrc');

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Limits the types of plugins that can be used. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-plugin-types
	 * @param string $mime  One or more plugin mime types, separate by spaces
	 *
	 * @return $this
	 */
	public function addPluginType($mime)
	{
		$this->addOption($mime, 'pluginTypes');

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
		$this->reportURI = (string)$uri;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * specifies an HTML sandbox policy that the user agent applies to
	 * the protected resource.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-sandbox
	 *
	 * @param bool $value
	 * @param array $flags An array of sandbox flags that can be added to the directive.
	 *
	 * @return $this
	 */
	public function setSandbox(bool $value = true, array $flags = null)
	{
		if (empty($this->sandbox) && ! count($flags))
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
	 * @param $uri
	 *
	 * @return $this
	 */
	public function addScriptSrc($uri)
	{
		$this->addOption($uri, 'scriptSrc');

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for CSS file sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-connect-src
	 * @param $uri
	 *
	 * @return $this
	 */
	public function addStyleSrc($uri)
	{
		$this->addOption($uri, 'styleSrc');

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
	 */
	protected function addOption($options, string $target)
	{
		if (is_array($options))
		{
			$this->{$target} = array_merge($this->{$target}, $options);
		}
		else
		{
			$this->{$target}[] = $options;
		}
	}

	//--------------------------------------------------------------------

}
