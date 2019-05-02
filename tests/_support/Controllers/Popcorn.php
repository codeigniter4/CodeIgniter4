<?php
namespace Tests\Support\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\Exceptions\HTTPException;

/**
 * This is a testing only controller, intended to blow up in multiple
 * ways to make sure we catch them.
 */
class Popcorn extends Controller
{

	use ResponseTrait;

	public function index()
	{
		return 'Hi there';
	}

	public function pop()
	{
		$this->respond('Oops', 567, 'Surprise');
	}

	public function popper()
	{
		throw new \RuntimeException('Surprise', 500);
	}

	public function weasel()
	{
		$this->respond('', 200);
	}

	public function oops()
	{
		$this->failUnauthorized();
	}

	public function goaway()
	{
		return redirect()->to('/');
	}

	// @see https://github.com/codeigniter4/CodeIgniter4/issues/1834
	public function index3()
	{
		$response = $this->response->setJSON([
			'lang' => $this->request->getLocale(),
		]);

		//      echo var_dump($this->response->getBody());
		return $response;
	}

	public function canyon()
	{
		echo 'Hello-o-o';
	}

	public function cat()
	{
	}

	public function json()
	{
		$this->responsd(['answer' => 42]);
	}

	public function xml()
	{
		$this->respond('<my><pet>cat</pet></my>');
	}

}
