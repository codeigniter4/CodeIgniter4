<?php namespace CodeIgniter\Filters;

use CodeIgniter\Config\BaseConfig;

class Filters
{
	/**
	 * The processed filters that will
	 * be used to check against.
	 * @var array
	 */
	protected $filters = [
		'before' => [],
		'after'  => []
	];

	/**
	 * The original config file
	 * @var BaseConfig
	 */
	protected $config;

	/**
	 * Whether we've done initial processing
	 * on the filter lists.
	 * @var bool
	 */
	protected $initialized = false;

	//--------------------------------------------------------------------

	public function __construct($config)
	{
		$this->config = $config;
	}

	//--------------------------------------------------------------------

	/**
	 * Runs through all of the filters for the specified
	 * uri and position.
	 *
	 * @param string $uri
	 * @param string $position
	 */
	public function run(string $uri, $position = 'before')
	{
	    $this->initialize($uri);
	}

	//--------------------------------------------------------------------

	/**
	 * Runs through our list of filters provided by the configuration
	 * object to get them ready for use, including getting uri masks
	 * to proper regex, removing those we can from the possibilities
	 * based on HTTP method, etc.
	 *
	 * We go ahead an process the entire tree because we'll need to
	 * run through both a before and after and don't want to double
	 * process the rows.
	 */
	public function initialize(string $uri = null)
	{
		if ($this->initialized === true)
		{
			return;
		}

	    $this->processGlobals();
	    $this->processMethods();
	    $this->processFilters($uri);

		$this->initialized = true;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the processed filters array.
	 *
	 * @return array
	 */
	public function getFilters()
	{
	    return $this->filters;
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Processors
	//--------------------------------------------------------------------

	protected function processGlobals()
	{
		if (! isset($this->config->globals) || ! is_array($this->config->globals))
		{
			return;
		}

		// Before
		if (isset($this->config->globals['before']))
		{
			$this->filters['before'] = array_merge($this->filters['before'], $this->config->globals['before']);
		}

		// After
		if (isset($this->config->globals['after']))
		{
			$this->filters['after'] = array_merge($this->filters['after'], $this->config->globals['after']);
		}
	}

	//--------------------------------------------------------------------

	protected function processMethods()
	{
		if (! isset($this->config->methods) || ! is_array($this->config->methods))
		{
			return;
		}

		// Request method won't be set for CLI-based requests
		$method = isset($_SERVER['REQUEST_METHOD'])
			? strtolower($_SERVER['REQUEST_METHOD'])
			: 'cli';

		if (array_key_exists($method, $this->config->methods))
		{
			$this->filters['before'] = array_merge($this->filters['before'], $this->config->methods[$method]);
			return;
		}
	}

	//--------------------------------------------------------------------

	protected function processFilters(string $uri = null)
	{
		if (! isset($this->config->filters) || ! count($this->config->filters))
		{
			return;
		}

		$uri = trim($uri, '/ ');

		$matches = [];

		foreach ($this->config->filters as $alias => $settings)
		{
			// Before
			if (isset($settings['before']))
			{
				foreach ($settings['before'] as $path)
				{
					// Prep it for regex
					$path = str_replace('/*', '*', $path);
					$path = trim(str_replace('*', '.+', $path), '/ ');

					if (preg_match('/'.$path.'/', $uri) !== 1)
					{
						continue;
					}

					$matches[] = $alias;
				}

				$this->filters['before'] = array_merge($this->filters['before'], $matches);
				$matches = [];
			}

			// After
			if (isset($settings['after']))
			{
				foreach ($settings['after'] as $path)
				{
					// Prep it for regex
					$path = str_replace('/*', '*', $path);
					$path = trim(str_replace('*', '.+', $path), '/ ');

					if (preg_match('/'.$path.'/', $uri) !== 1)
					{
						continue;
					}

					$matches[] = $alias;
				}

				$this->filters['after'] = array_merge($this->filters['after'], $matches);
			}
		}
	}

	//--------------------------------------------------------------------

}
