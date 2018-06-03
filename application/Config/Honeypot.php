<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Honeypot extends BaseConfig
{

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