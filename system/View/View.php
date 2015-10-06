<?php namespace CodeIgniter\View;

use CodeIgniter\Loader;

/**
 * Class View
 *
 * @todo Add data escaping in setData and setVar methods.
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
	 * Instance of CodeIgniter\Loader for when
	 * we need to attempt to find a view
	 * that's not in standard place.
	 * @var
	 */
	protected $loader;

	//--------------------------------------------------------------------

	public function __construct()
	{
//	    $this->loader = $loader;
	}

	//--------------------------------------------------------------------



	/**
	 * Builds the output based upon a file name and any
	 * data that has already been set.
	 *
	 * @param string $view
	 *
	 * @return string
	 */
	public function render(string $view, array $data=[]): string
	{
		$file = APPPATH.'views/'.str_replace('.php', '', $view).'.php';

		if (! file_exists($file))
		{
			throw new \InvalidArgumentException('View file not found: '. $file);
		}

		if (! empty($data))
		{
			$this->data = array_merge($this->data, $data);
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
	 *
	 * @return RenderableInterface
	 */
	public function setData(array $data=[]): RenderableInterface
	{
		$this->data = array_merge($this->data, $data);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets a single piece of view data.
	 *
	 * @param string $name
	 * @param null   $value
	 *
	 * @return RenderableInterface
	 */
	public function setVar(string $name, $value=null): RenderableInterface
	{
		$this->data[$name] = $value;

		return $this;
	}

	//--------------------------------------------------------------------
}
