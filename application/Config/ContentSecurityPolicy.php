<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Class ContentSecurityPolicyConfig
 *
 * Stores the default settings for the ContentSecurityPolicy, if you
 * choose to use it. The values here will be read in and set as defaults
 * for the site. If needed, they can be overridden on a page-by-page basis.
 *
 * @package Config
 */
class ContentSecurityPolicy extends BaseConfig
{
	public $reportOnly = false;

	public $defaultSrc = 'none';

	public $scriptSrc = 'self';

	public $styleSrc = 'self';

	public $imageSrc = 'self';

	public $baseURI = 'none';

	public $childSrc = null;

	public $connectSrc = 'self';

	public $fontSrc = null;

	public $formAction = null;

	public $frameAncestors = null;

	public $mediaSrc = null;

	public $objectSrc = null;
	
	public $manifestSrc = null;

	public $pluginTypes = null;

	public $reportURI = null;

	public $sandbox = false;

	public $upgradeInsecureRequests = false;
}
