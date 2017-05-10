<?php

namespace CodeIgniter\Encryption;

class MockEncryption extends Encryption
{

	/**
	 * __get_params()
	 *
	 * Allows public calls to the otherwise protected _get_params().
	 */
	public function __getParams($params)
	{
		return $this->getParams($params);
	}

	// --------------------------------------------------------------------

	/**
	 * get_key()
	 *
	 * Allows checking for key changes.
	 */
	public function getKey()
	{
		return $this->_key;
	}

	// --------------------------------------------------------------------

	/**
	 * __driver_get_handle()
	 *
	 * Allows checking for _openssl_get_handle()
	 */
	public function handlerGetHandle($driver, $cipher, $mode)
	{
		return $this->{$driver . 'GetHandle'}($cipher, $mode);
	}

}
