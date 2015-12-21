<?php namespace App\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Class ContentSecurityPolicyConfig
 *
 * Stores the default settings for the ContentSecurityPolicy, if you
 * choose to use it. The values here will be read in and set as defaults
 * for the site. If needed, they can be overridden on a page-by-page basis.
 *
 * @package App\Config
 */
class ContentSecurityPolicyConfig extends BaseConfig
{
	public $reportOnly = false;

	public $base_uri = null;

	public $childSrc = null;

	public $connectSrc = null;

	public $defaultSrc = null;

	public $fontSrc = null;

	public $formAction = null;

	public $frameAncestors = null;

	public $imageSrc = null;

	public $mediaSrc = null;

	public $objectSrc = null;

	public $pluginTypes = null;

	public $reportURI = null;

	public $sandbox = false;

	public $scriptSrc = null;

	public $styleSrc = null;

	public $upgradeInsecureRequests = false;
}
