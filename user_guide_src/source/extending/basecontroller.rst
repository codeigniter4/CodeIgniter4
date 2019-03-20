************************
Extending the Controller
************************

CodeIgniter's core Controller should not be changed, but a default class extension is provided for you at
**app/Controllers/BaseController.php**. Any new controllers you make should extend ``BaseController`` to take
advantage of preloaded components and any additional functionality you provide::

	<?php namespace App\Controllers;
	
	use CodeIgniter\Controller;
	
	class Home extends BaseController {
	
	}

Preloading Components
=====================

The base controller is a great place to load any helpers, models, libraries, services, etc. that you intend to
use every time your project runs. Helpers should be added to the pre-defined $helpers array. For example, if
you want the HTML and Text helpers universally available::

	protected $helpers = ['html', 'text'];

Any other components to load or data to process should be added to the constructor ``initController()``. For
example, if your project uses the Session Library heavily you may want to initiate it here::

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);
		
		$this->session = \Config\Services::session();
	}

Additional Methods
==================

The base controller is not routable (system config routes it to 404 Page Not Found), but as an added security
measure **all** new methods should be declared as protected or private and only accessed through the
controllers you create that extend BaseController.

Other Options
=============

You may find that you need more than one base controller, in which case you can create new base controllers to
be extended - just make sure any controller that you make extends the correct base. For example if your project
has an involved public interface and a simple administrative portal, you may want to extend BaseController to
the public controllers and AdminController to any administrative controllers.

If you do not want to use the base controller you may bypass it by having your controllers extend the system
Controller instead::

	class Home extends Controller
	{
	
	}
