<?php namespace CodeIgniter\View;

/**
 * Interface RenderableInterface
 *
 * The interface used for displaying views and/or theme files.
 *
 * @package CodeIgniter\View
 */
interface RenderableInterface {

	/**
	 * Builds the output based upon a file name and any
	 * data that has already been set.
	 *
	 * @param string $view
	 * @param array  $options  Reserved for 3rd-party uses since
	 *                         it might be needed to pass additional info
	 *                         to other template engines.
	 *
	 * @return string
	 */
	public function render(string $view, array $options=[]): string;

	//--------------------------------------------------------------------

	/**
	 * Sets several pieces of view data at once.
	 *
	 * @param array $data
	 * @param string $context The context to escape it for: html, css, js, url
	 *                        If 'raw', no escaping will happen
	 *
	 * @return RenderableInterface
	 */
	public function setData(array $data=[], string $context=null): self;

	//--------------------------------------------------------------------

	/**
	 * Sets a single piece of view data.
	 *
	 * @param string $name
	 * @param null   $value
	 * @param string $escape The context to escape it for: html, css, js, url
	 *                        If 'raw' no escaping will happen
	 *
	 * @return RenderableInterface
	 */
	public function setVar(string $name, $value=null, string $context=null): self;

	//--------------------------------------------------------------------

}