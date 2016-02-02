<?php namespace CodeIgniter\Debug;

use CodeIgniter\Config\BaseConfig;

class Toolbar
{
	/**
	 * Collectors to be used and displayed.
	 * @var array
	 */
	protected $collectors = [];

	//--------------------------------------------------------------------

	public function __construct(BaseConfig $config)
	{
		$this->collectors = $config->toolbarCollectors;
	}

	//--------------------------------------------------------------------


	public function run(): string
	{
		// Data items used within the view.
		$collectors = $this->collectors;

		global $totalTime;
		$totalTime = $totalTime * 1000;

		ob_start();
		include(dirname(__FILE__).'/Toolbar/View/toolbar.tpl.php');
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	//--------------------------------------------------------------------


}
