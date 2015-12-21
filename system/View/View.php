<?php namespace CodeIgniter\View;

use App\Config\Services;
use CodeIgniter\Loader;

/**
 * Class View
 *
 * @todo integrate parsing somehow
 *
 * @package CodeIgniter\View
 */
class View implements RenderableInterface {

	/**
	 * Data that is made available to the views.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * The base directory to look in for our views.
	 *
	 * @var
	 */
	protected $viewPath;
	/**
	 * Instance of CodeIgniter\Loader for when
	 * we need to attempt to find a view
	 * that's not in standard place.
	 * @var
	 */
	protected $loader;

	//--------------------------------------------------------------------

	public function __construct(string $viewPath=null, $loader=null)
	{
		$this->viewPath = rtrim($viewPath, '/ ').'/';

		if (! is_null($loader))
		{
			$this->loader = $loader;
		}
		else
		{
			$this->loader = Services::loader(true);
		}
	}

	//--------------------------------------------------------------------



	/**
	 * Builds the output based upon a file name and any
	 * data that has already been set.
	 *
	 * @param string $view
	 * @param array  $options  // Unused in this implementation
	 *
	 * @return string
	 */
	public function render(string $view, array $options=[]): string
	{
		$view = str_replace('.php', '', $view).'.php';

		$file = $this->viewPath.$view;

		if (! file_exists($file))
		{
			$file = $this->loader->locateFile($view, 'views');
		}

		// locateFile will return an empty string if the file cannot be found.
		if (empty($file))
		{
			throw new \InvalidArgumentException('View file not found: '. $file);
		}

		// Make our view data available to the view.
		extract($this->data);

		ob_start();

		include($file);

		$output = ob_get_contents();
		@ob_end_clean();

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets several pieces of view data at once.
	 *
	 * @param array $data
	 * @param string $context The context to escape it for: html, css, js, url
	 *                        If null, no escaping will happen
	 *
	 * @return RenderableInterface
	 */
	public function setData(array $data=[], string $context=null): RenderableInterface
	{
		if (! empty($context))
		{
			$data = \esc($data, $context);
		}

		$this->data = array_merge($this->data, $data);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets a single piece of view data.
	 *
	 * @param string $name
	 * @param null   $value
	 * @param string $context The context to escape it for: html, css, js, url
	 *                        If null, no escaping will happen
	 *
	 * @return RenderableInterface
	 */
	public function setVar(string $name, $value=null, string $context=null): RenderableInterface
	{
		if (! empty($context))
		{
			$value = \esc($value, $context);
		}

		$this->data[$name] = $value;

		return $this;
	}

	//--------------------------------------------------------------------

}
