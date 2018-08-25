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
	 * Honeypot Label Content
	 * @var String
	 */
    public $label = 'Fill This Field';

    /**
	 * Honeypot Field Name 
	 * @var String
	 */
    public $name = 'honeypot';

    /**
	 * Honeypot HTML Template 
	 * @var String
	 */
    public $template = '<label>{label}</label><input type="text" name="{name}" value=""/>';
}