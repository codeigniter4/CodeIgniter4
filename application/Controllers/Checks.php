<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Config\Services;
use CodeIgniter\Controller;
use CodeIgniter\Model;
use Config\Database;
use Tests\Support\Models\JobModel;

class Checks extends Controller
{
	use ResponseTrait;

	public function index()
	{
		session()->start();
	}


	public function escape()
	{
		$db = Database::connect();
		$db->initialize();

		$jobs = $db->table('job')
						 ->whereNotIn('name', ['Politician', 'Accountant'])
						 ->get()
						 ->getResult();

		die(var_dump($jobs));
	}

	public function password()
	{
		$db = Database::connect();
		$db->initialize();

		$result = $db->table('misc')
					->insert([
						'key' => 'password',
						'value' => '$2y$10$ErQlCj/Mo10il.FthAm0WOjYdf3chZEGPFqaPzjqOX2aj2uYf5Ihq'
					]);

		die(var_dump($result));
	}


	public function forms()
	{
		helper('form');

		var_dump(form_open());
	}

	public function api()
	{
		$data = array(
			"total_users" => 3,
			"users" => array(
				array(
					"id" => 1,
					"name" => "Nitya",
					"address" => array(
						"country" => "India",
						"city" => "Kolkata",
						"zip" => 700102,
					)
				),
				array(
					"id" => 2,
					"name" => "John",
					"address" => array(
						"country" => "USA",
						"city" => "Newyork",
						"zip" => "NY1234",
					)
				),
				array(
					"id" => 3,
					"name" => "Viktor",
					"address" => array(
						"country" => "Australia",
						"city" => "Sydney",
						"zip" => 123456,
					)
				),
			)
		);

		return $this->respond($data);
	}

	public function db()
	{
		$db = Database::connect();
		$db->initialize();

		$query = $db->prepare(function($db){
			return $db->table('user')->insert([
				'name' => 'a',
				'email' => 'b@example.com',
				'country' => 'x'
			]);
		});

		$query->execute('foo', 'foo@example.com', 'US');
	}

	public function format()
	{
		echo '<pre>';
		var_dump($this->response->getHeaderLine('content-type'));
	}

	public function model()
	{
	    $model = new class() extends Model {
	        protected $table = 'job';
        };

	    $results = $model->findAll();

	    $developer = $model->findWhere('name', 'Developer');

	    $politician = $model->find(3);

	}

    public function curl()
    {
        $client = Services::curlrequest([
            'debug' => true,
            'follow_redirects' => true,
            'json' => ['foo' => 'bar']
        ]);

        echo '<pre>';
        $response = $client->request('PUT', 'http://ci4.dev/checks/catch');
        echo $response->getBody();
    }

    // Simply echos back what's given in the body.
    public function catch()
    {
        $body = print_r($this->request->getRawInput(), true);
        echo $body;
    }


}
