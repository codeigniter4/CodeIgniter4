<?php namespace CodeIgniter\Cache;

/**
 * Class Cache
 *
 * A factory for loading the desired
 *
 * @package CodeIgniter\Cache
 */
class CacheFactory
{
	/**
	 * Attempts to create the desired cache handler, based upon the
	 *
	 * @param        $config
	 * @param string $handler
	 * @param string $backup
	 *
	 * @return mixed
	 */
	public static function getHandler($config, string $handler = null, string $backup = null)
	{
	    if (! isset($config->validHandlers) || ! is_array($config->validHandlers))
		{
			throw new \InvalidArgumentException('Cache config must have an array of $validHandlers.');
		}

		if (! isset($config->handler) || ! isset($config->backupHandler))
		{
			throw new \InvalidArgumentException('Cache config must have a handler and backupHanlder set.');
		}

		$handler = ! empty($handler) ? $handler : $config->handler;
		$backup  = ! empty($backup)  ? $backup  : $config->backupHandler;

		if (! in_array($handler, $config->validHandlers) || ! in_array($backup, $config->validHandlers))
		{
			throw new \InvalidArgumentException('Cache config has an invalid handler or backup handler specified.');
		}

		// Get an instance of our handler.
		$adapter = new $config->validHandlers[$handler]($config);

		if (! $adapter->isSupported())
		{
			$adapter = new $config->validHandlers[$backup]($config);

			if (! $adapter->isSupported())
			{
				// Log stuff here, don't throw exception. No need to raise a fuss.

				// Fall back to the dummy adapter.
				$adapter = new $config->validHandler['dummy']();
			}
		}

		$adapter->initialize();

		return $adapter;
	}

	//--------------------------------------------------------------------

}