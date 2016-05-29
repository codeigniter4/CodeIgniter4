<?php

if (! function_exists('site_url'))
{
	/**
	 * 
	 * 
	 * @param string      $path
	 * @param string|null $scheme
	 *
	 * @return string
	 */
	function site_url(string $path = '', string $scheme = null): string
	{
		$config = new \Config\App();

		$url = ! empty($config->baseURL)
			? new \CodeIgniter\HTTP\URI($config->baseURL)
			: clone(\CodeIgniter\Services::request()->uri);

		// Add index page
		if (! empty($config->indexPage))
		{
			$path = rtrim($config->indexPage, '/').'/'.$path;
		}

		$url->setPath($path);

		if (! empty($scheme))
		{
			$url->setScheme($scheme);
		}

		return (string)$url;
	}
}

//--------------------------------------------------------------------

if (! function_exists('base_url'))
{
	function base_url(string $path = '', string $scheme = null): string
	{
		$config = new \Config\App();

		$url = ! empty($config->baseURL)
			? new \CodeIgniter\HTTP\URI($config->baseURL)
			: clone(\CodeIgniter\Services::request()->uri);

		$url->setPath($path);

		if (! empty($scheme))
		{
			$url->setScheme($scheme);
		}

		return (string)$url;
	}
}

//--------------------------------------------------------------------

if (! function_exists('current_url'))
{
	/**
	 * Current URL
	 *
	 * Returns the full URL (including segments) of the page where this
	 * function is placed
	 *
	 * @return	string
	 */
	function current_url(bool $returnObject = false)
	{
		return $returnObject === true
			? \CodeIgniter\Services::request()->uri
			: (string)\CodeIgniter\Services::request()->uri;
	}
}
//--------------------------------------------------------------------

if (! function_exists('uri_string'))
{
	/**
	 * URL String
	 *
	 * Returns the URI segments.
	 *
	 * @return	string
	 */
	function uri_string(): string
	{
		return \CodeIgniter\Services::request()->uri->getPath();
	}
}

//--------------------------------------------------------------------

if (! function_exists('index_page'))
{
	/**
	 * Index page
	 *
	 * Returns the "index_page" from your config file
	 *
	 * @return	string
	 */
	function index_page()
	{
		$config = new \Config\App();

		return $config->indexPage;
	}
}
