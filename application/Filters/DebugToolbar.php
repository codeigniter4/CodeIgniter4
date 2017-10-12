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

                        helper('filesystem');
                        helper('text');
                        $fileName = 'debugbar_' . random_string(); 
                        write_file('./' . $fileName, $output, 'w+');
                        return $response->appendBody( PHP_EOL .
'<div id="ci_toolbar"></div>
<script>
    document.addEventListener(\'DOMContentLoaded\', loadDoc, false);

    function loadDoc() {
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          var x = document.body.innerHTML;
          document.body.innerHTML = x + this.responseText;
          var arr = document.body.getElementsByTagName(\'script\')
          for (var n = 0; n < arr.length; n++)
            eval(arr[n].innerHTML)//run script inside div          
        }
      };
      xhttp.open("GET", "/getdebugbar.php?f='.$fileName.'", true);
      xhttp.send();
    }
</script>');
		}
	}

	//--------------------------------------------------------------------
}
