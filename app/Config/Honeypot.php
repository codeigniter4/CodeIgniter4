<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Honeypot extends BaseConfig
{

	/**
	 * Makes Honeypot visible or not to human
	 *
	 * @var boolean
	 */
	public $hidden = true;
	
	/**
         * Hide the Honeypot. If not set or null, the style parameter will be use. Otherwise it will be set the class parametr by inserted string.
         * Example:
         * public $hiddenBy = 'hidden';
         * 
         * @var ?string 
         */
        public $hiddenByClass = null;
	
	/**
         * Div wrapper of honeypot.
         * 
         * @var string 
         */
        public $container = '<div style="display:none">%s</div>';
	
	/**
	 * Honeypot Label Content
	 *
	 * @var string
	 */
	public $label = 'Fill This Field';

	/**
	 * Honeypot Field Name
	 *
	 * @var string
	 */
	public $name = 'honeypot';

	/**
	 * Honeypot HTML Template
	 *
	 * @var string
	 */
	public $template = '<label>{label}</label><input type="text" name="{name}" value=""/>';
}
