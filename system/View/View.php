<?php namespace CodeIgniter\View;

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

	public function __construct(string $viewPath=null)
	{
		$this->viewPath = rtrim($viewPath, '/ ').'/';
	}

	//--------------------------------------------------------------------



	/**
	 * Builds the output based upon a file name and any
	 * data that has already been set.
	 *
	 * @param string $view
	 * @param array  $data
	 * @param bool   $escape Whether the $data values should be escaped
	 *
	 * @return string
	 */
	public function render(string $view, array $data=[], bool $escape=true): string
	{
		$file = $this->viewPath.str_replace('.php', '', $view).'.php';

		if (! file_exists($file))
		{
			throw new \InvalidArgumentException('View file not found: '. $file);
		}

		if (! empty($data))
		{
			if ($escape === true)
			{
				$data = $this->escapeData($data);
			}

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
	 * @param bool  $escape Whether values should be escaped
	 *
	 * @return RenderableInterface
	 */
	public function setData(array $data=[], bool $escape=true): RenderableInterface
	{
		if ($escape === true)
		{
			$data = $this->escapeData($data);
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
	 * @param bool   $escape Whether value should be escaped.
	 *
	 * @return RenderableInterface
	 */
	public function setVar(string $name, $value=null, bool $escape=true): RenderableInterface
	{
		if ($escape === true)
		{
			$value = $this->escapeData($value);
		}

		$this->data[$name] = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Performs simple auto-escaping of data for security reasons.
	 * Might consider making this more complex at a later date.
	 *
	 * If $data is a string, then it simply escapes and returns it.
	 * If $data is an array, then it loops over it, escaping each
	 * 'value' of the key/value pairs.
	 *
	 * @param $data
	 *
	 * @return $data
	 */
	protected function escapeData($data)
	{
		if (is_array($data))
		{
			foreach ($data as $key => &$value)
			{
				$value = htmlspecialchars($value, ENT_SUBSTITUTE, 'UTF-8');
			}
		}
		else if (is_string($data))
		{
			$data = htmlspecialchars($data, ENT_SUBSTITUTE, 'UTF-8');
		}

		return $data;
	}

	//--------------------------------------------------------------------

}
