<?php namespace CodeIgniter\Debug\Toolbar\Collectors;

use Config\Services;

class Routes extends BaseCollector
{
	/**
	 * Whether this collector has data that can
	 * be displayed in the Timeline.
	 *
	 * @var bool
	 */
	protected $hasTimeline = false;

	/**
	 * Whether this collector needs to display
	 * content in a tab or not.
	 *
	 * @var bool
	 */
	protected $hasTabContent = true;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected $title = 'Routes';

	//--------------------------------------------------------------------

	/**
	* Builds and returns the HTML needed to fill a tab to display
	* within the Debug Bar
	*
	* @return string
	*/
	public function display(): string
	{
		$routes = Services::routes(true);
		$router = Services::router(null, true);

		$output = "<h3>Matched Route</h3>";

		$output .= "<table><tbody>";

		if ($match = $router->getMatchedRoute())
		{
			$output .= "<tr><td>{$match[0]}</td>";
			$output .= "<td>{$match[1]}</td></tr>";
		}


		$output .= "<tr><td>Directory:</td><td>".htmlspecialchars($router->directory())."</td></tr>";
		$output .= "<tr><td>Controller:</td><td>".htmlspecialchars($router->controllerName())."</td></tr>";
		$output .= "<tr><td>Method:</td><td>".htmlspecialchars($router->methodName())."</td></tr>";
		$output .= "<tr><td>Params:</td><td>".print_r($router->params(), true)."</td></tr>";

		$output .= "</table></tbody>";

		$output .= "<h3>Defined Routes</h3>";

		$output .= "<table><tbody>";

		$routes = $routes->getRoutes();

		foreach ($routes as $from => $to)
		{
			$output .= "<tr>";
			$output .= "<td>".htmlspecialchars($from)."</td>";
			$output .= "<td>".htmlspecialchars($to)."</td>";
			$output .= "</tr>";
		}

		$output .= "</tbody></table>";

		return $output;
	}

	//--------------------------------------------------------------------
}
