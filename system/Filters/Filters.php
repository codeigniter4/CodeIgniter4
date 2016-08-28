<?php namespace CodeIgniter\Filters;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Zend\Escaper\Exception\RuntimeException;

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
	 * The active IncomingRequest or CLIRequest
	 * @var RequestInterface
	 */
	protected $request;

	/**
	 * The active Response instance
	 * @var ResponseInterface
	 */
	protected $response;

	/**
	 * Whether we've done initial processing
	 * on the filter lists.
	 * @var bool
	 */
	protected $initialized = false;

	//--------------------------------------------------------------------

	public function __construct($config, RequestInterface $request, ResponseInterface $response)
	{
		$this->config   = $config;
		$this->request  =& $request;
		$this->response =& $response;
	}

	//--------------------------------------------------------------------

	/**
	 * Runs through all of the filters for the specified
	 * uri and position.
	 *
	 * @param string $uri
	 * @param string $position
	 *
	 * @return \CodeIgniter\HTTP\RequestInterface|\CodeIgniter\HTTP\ResponseInterface|mixed
	 */
	public function run(string $uri, $position = 'before')
	{
	    $this->initialize($uri);

		foreach ($this->filters[$position] as $alias => $rules)
		{
			if (is_numeric($alias) && is_string($rules))
			{
				$alias = $rules;
			}

			if (! array_key_exists($alias, $this->config->aliases))
			{
				throw new \InvalidArgumentException("'{$alias}' filter must have a matching alias defined.");
			}

			$class = new $this->config->aliases[$alias]();

			if (! $class instanceof FilterInterface)
			{
				throw new \RuntimeException(get_class($class). ' must implement CodeIgniter\Filters\FilterInterface.');
			}

			if ($position == 'before')
			{
				$result = $class->before($this->request);

				if ($result instanceof RequestInterface)
				{
					$this->request = $result;
					continue;
				}

				if (empty($result))
				{
					continue;
				}

				return $result;
			}

			elseif ($position == 'after')
			{
				$result = $class->after($this->request, $this->response);

				if ($result instanceof ResponseInterface)
				{
					$this->response = $result;
					continue;
				}
			}
		}

		return $position == 'before'
				? $this->request
				: $this->response;
	}

	//--------------------------------------------------------------------

	/**
	 * Runs through our list of filters provided by the configuration
	 * object to get them ready for use, including getting uri masks
	 * to proper regex, removing those we can from the possibilities
	 * based on HTTP method, etc.
	 *
	 * The resulting $this->filters is an array of only filters
	 * that should be applied to this request.
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

	    $this->processGlobals($uri);
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

	protected function processGlobals(string $uri = null)
	{
		if (! isset($this->config->globals) || ! is_array($this->config->globals))
		{
			return;
		}

		// Before
		if (isset($this->config->globals['before']))
		{
			// Take any 'except' routes into consideration
			foreach ($this->config->globals['before'] as $alias => $rules)
			{
				if (! is_array($rules) || ! array_key_exists('except', $rules))
				{
					continue;
				}

				$rules = $rules['except'];

				foreach ($rules as $path)
				{
					// Prep it for regex
					$path = str_replace('/*', '*', $path);
					$path = trim(str_replace('*', '.+', $path), '/ ');

					// Path doesn't match the URI? continue on...
					if (preg_match('/'.$path.'/', $uri, $match) !== 1)
					{
						continue;
					}

					unset($this->config->globals['before'][$alias]);
					break;
				}
			}

			$this->filters['before'] = array_merge($this->filters['before'], $this->config->globals['before']);
		}

		// After
		if (isset($this->config->globals['after']))
		{
			// Take any 'except' routes into consideration
			foreach ($this->config->globals['after'] as $alias => $rules)
			{
				if (! is_array($rules) || ! array_key_exists('except', $rules))
				{
					continue;
				}

				$rules = $rules['except'];

				if (is_string($rules))
				{
					$rules = [$rules];
				}

				foreach ($rules as $path)
				{
					// Prep it for regex
					$path = str_replace('/*', '*', $path);
					$path = trim(str_replace('*', '.+', $path), '/ ');

					// Path doesn't match the URI? continue on...
					if (preg_match('/'.$path.'/', $uri, $match) !== 1)
					{
						continue;
					}

					unset($this->config->globals['after'][$alias]);
					break;
				}
			}

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
