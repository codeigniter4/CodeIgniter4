<?php namespace CodeIgniter\Config;

class View {

	protected $coreFilters = [
		'abs'               => '\CodeIgniter\View\Filters::abs',
		'capitalize'        => '\CodeIgniter\View\Filters::capitalize',
		'date'              => '\CodeIgniter\View\Filters::date',
		'date_modify'       => '\CodeIgniter\View\Filters::date_modify',
		'default'           => '\CodeIgniter\View\Filters::default',
		'esc'               => '\CodeIgniter\View\Filters::esc',
		'excerpt'           => '\CodeIgniter\View\Filters::excerpt',
		'highlight'         => '\CodeIgniter\View\Filters::highlight',
		'highlight_code'    => '\CodeIgniter\View\Filters::highlight_code',
		'limit_words'       => '\CodeIgniter\View\Filters::limit_words',
		'limit_chars'       => '\CodeIgniter\View\Filters::limit_chars',
		'lower'             => '\CodeIgniter\View\Filters::lower',
		'nl2br'             => '\CodeIgniter\View\Filters::nl2br',
		'number_format'     => '\CodeIgniter\View\Filters::number_format',
		'prose'             => '\CodeIgniter\View\Filters::prose',
		'round'             => '\CodeIgniter\View\Filters::round',
		'strip_tags'        => '\CodeIgniter\View\Filters::strip_tags',
		'title'             => '\CodeIgniter\View\Filters::title',
		'upper'             => '\CodeIgniter\View\Filters::upper',
	];

	protected $corePlugins = [
		'current_url'       => '\CodeIgniter\View\Plugins::currentURL',
		'previous_url'      => '\CodeIgniter\View\Plugins::previousURL',
		'mailto'            => '\CodeIgniter\View\Plugins::mailto',
		'safe_mailto'       => '\CodeIgniter\View\Plugins::safeMailto',
    ];

	public function __construct()
	{
	    $this->filters = array_merge($this->filters, $this->coreFilters);
	    $this->plugins = array_merge($this->plugins, $this->corePlugins);
	}

}
