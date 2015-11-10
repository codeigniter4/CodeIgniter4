<?php namespace CodeIgniter\Log;

use Psr\Log\LoggerInterface;

trait LoggerAwareTrait {

	/**
	 * Sets a logger instance on the object
	 *
	 * @param LoggerInterface $logger
	 *
	 * @return null
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	//--------------------------------------------------------------------

}
