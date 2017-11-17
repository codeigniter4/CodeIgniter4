<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\App;
use Config\Services;

class DebugToolbar implements FilterInterface
{
	/**
	 * We don't need to do anything here.
	 *
	 * @param RequestInterface|\CodeIgniter\HTTP\IncomingRequest $request
	 *
	 * @return mixed
	 */
	public function before(RequestInterface $request)
	{

	}

	//--------------------------------------------------------------------

	/**
	 * If the debug flag is set (CI_DEBUG) then collect performance
	 * and debug information and display it in a toolbar.
	 *
	 * @param RequestInterface|\CodeIgniter\HTTP\IncomingRequest $request
	 * @param ResponseInterface|\CodeIgniter\HTTP\Response $response
	 *
	 * @return mixed
	 */
	public function after(RequestInterface $request, ResponseInterface $response)
	{
		$format = $response->getHeaderLine('content-type');

		if ( ! is_cli() && CI_DEBUG && strpos($format, 'html') !== false)
		{
			global $app;

			$toolbar = Services::toolbar(new App());
			$stats   = $app->getPerformanceStats();
			$output  = $toolbar->run(
				$stats['startTime'],
				$stats['totalTime'],
				$stats['startMemory'],
				$request,
				$response
			);

			helper(['filesystem', 'url']);

			// Updated to time() so we can get history
			$time = time();

			if (! is_dir(WRITEPATH.'debugbar'))
			{
				mkdir(WRITEPATH.'debugbar', 0777);
			}

			write_file(WRITEPATH .'debugbar/'.'debugbar_' . $time, $output, 'w+');

			$script = PHP_EOL
				. '<script type="text/javascript" id="debugbar_loader" '
				. 'data-time="' . $time . '" '
				. 'src="' . rtrim(site_url(), '/') . '?debugbar"></script>'
				. PHP_EOL;

			if (strpos($response->getBody(), '</body>') !== false)
			{
				return $response->setBody(str_replace('</body>', $script . '</body>',
					$response->getBody()));
			}

			return $response->appendBody($script);
		}
	}

	//--------------------------------------------------------------------
}
