<?php namespace CodeIgniter\Log\Handlers;

use CodeIgniter\Log\Handlers\BaseHandler;
use CodeIgniter\Log\Handlers\HandlerInterface;

class FileHandler extends BaseHandler implements HandlerInterface
{
	protected $path;

	protected $fileExtension;

	protected $filePermissions;

	//--------------------------------------------------------------------

	public function __construct(array $config = [])
	{
		parent::__construct($config);

		$this->path = $config['path'] ?? WRITEPATH.'logs/';

		$this->fileExtension = $config['fileExtension'] ?? 'php';
		$this->fileExtension = ltrim($this->fileExtension, '.');

		$this->filePermissions = $config['filePermissions'] ?? 0644;
	}

	//--------------------------------------------------------------------

	/**
	 * Handles logging the message.
	 * If the handler returns false, then execution of handlers
	 * will stop. Any handlers that have not run, yet, will not
	 * be run.
	 *
	 * @param $level
	 * @param $message
	 *
	 * @return bool
	 */
	public function handle($level, $message): bool
	{
		$filepath = $this->path.'log-'.date('Y-m-d').'.'.$this->fileExtension;

		$msg = '';

		if ( ! file_exists($filepath))
		{
			$newfile = true;

			// Only add protection to php files
			if ($this->fileExtension === 'php')
			{
				$msg .= "<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>\n\n";
			}
		}

		if ( ! $fp = @fopen($filepath, 'ab'))
		{
			return false;
		}

		// Instantiating DateTime with microseconds appended to initial date is needed for proper support of this format
		if (strpos($this->dateFormat, 'u') !== false)
		{
			$microtime_full  = microtime(true);
			$microtime_short = sprintf("%06d", ($microtime_full - floor($microtime_full)) * 1000000);
			$date            = new DateTime(date('Y-m-d H:i:s.'.$microtime_short, $microtime_full));
			$date            = $date->format($this->dateFormat);
		}
		else
		{
			$date = date($this->dateFormat);
		}

		$msg .= strtoupper($level).' - '.$date.' --> '.$message."\n";

		flock($fp, LOCK_EX);

		for ($written = 0, $length = strlen($msg); $written < $length; $written += $result)
		{
			if (($result = fwrite($fp, substr($msg, $written))) === false)
			{
				break;
			}
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		if (isset($newfile) && $newfile === true)
		{
			chmod($filepath, $this->filePermissions);
		}

		return is_int($result);
	}
	
	//--------------------------------------------------------------------
	
	
}
