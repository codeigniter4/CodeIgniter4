<?php namespace CodeIgniter\HTTP;

use App\Config\AppConfig;

interface RequestInterface {

	public function __construct(AppConfig $config, URI $uri=null);

	//--------------------------------------------------------------------


	/**
	 * Determines if this request was made from the command line (CLI).
	 *
	 * @return bool
	 */
	public function isCLI(): bool;

	//--------------------------------------------------------------------

	/**
	 * Test to see if a request contains the HTTP_X_REQUESTED_WITH header.
	 *
	 * @return bool
	 */
	public function isAJAX(): bool;

	//--------------------------------------------------------------------

	/**
	 * Gets the user's IP address.
	 *
	 * @return string IP address
	 */
	public function ipAddress(): string;

	//--------------------------------------------------------------------

	/**
	 * Validate an IP address
	 *
	 * @param        $ip     IP Address
	 * @param string $which  IP protocol: 'ipv4' or 'ipv6'
	 *
	 * @return bool
	 */
	public function validIP(string $ip, string $which = null): bool;

	//--------------------------------------------------------------------

	/**
	 * Get the request method.
	 *
	 * @param bool|false $upper Whether to return in upper or lower case.
	 *
	 * @return string
	 */
	public function method($upper = false): string;

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from the $_SERVER array.
	 *
	 * @param null $index   Index for item to be fetched from $_SERVER
	 * @param null $filter  A filter name to be applied
	 * @return mixed
	 */
	public function server($index = null, $filter = null);

	//--------------------------------------------------------------------


}