<?php namespace CodeIgniter\View;

class Plugins {

	public static function currentURL(array $params=[])
	{
		if (! function_exists('current_url')) helper('url');

		return current_url();
	}

	//--------------------------------------------------------------------

	public static function previousURL(array $params=[])
	{
		if (! function_exists('previous_url')) helper('url');

		return previous_url();
	}

	//--------------------------------------------------------------------

	public static function mailto(array $params=[])
	{
		if (! function_exists('mailto')) helper('url');

		$email = $params['email'] ?? '';
		$title = $params['title'] ?? '';
		$attrs = $params['attributes'] ?? '';

		return mailto($email, $title, $attrs);
	}

	//--------------------------------------------------------------------

	public static function safeMailto(array $params=[])
	{
		if (! function_exists('safe_mailto')) helper('url');

		$email = $params['email'] ?? '';
		$title = $params['title'] ?? '';
		$attrs = $params['attributes'] ?? '';

		return safe_mailto($email, $title, $attrs);
	}

	//--------------------------------------------------------------------
}
