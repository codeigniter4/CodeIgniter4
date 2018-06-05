<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Honeypot extends BaseConfig
{

	/**
	 * Makes Honeypot visible or not to human
	 * 
	 * @var boolean
	 */
	public $hidden = '';
    /**
	 * Honeypot Label Content
	 * @var String
	 */
    public $label = '';

    /**
	 * Honeypot Field Name 
	 * @var String
	 */
    public $name = '';

    /**
	 * Honeypot HTML Template 
	 * @var String
	 */
    public $template = '';
}