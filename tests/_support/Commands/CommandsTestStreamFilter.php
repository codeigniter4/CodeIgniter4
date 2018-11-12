<?php namespace CodeIgniter\Commands;

class CommandsTestStreamFilter extends \php_user_filter
{
	public static $buffer = '';

	public function filter($in, $out, &$consumed, $closing)
	{
		while ($bucket = stream_bucket_make_writeable($in))
		{
			self::$buffer .= $bucket->data;
			$consumed     += $bucket->datalen;
		}
		return PSFS_PASS_ON;
	}
}

stream_filter_register('CommandsTestStreamFilter', 'CodeIgniter\Commands\CommandsTestStreamFilter');
