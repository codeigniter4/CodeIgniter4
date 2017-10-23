<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Config\Services;
use CodeIgniter\Controller;
use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use Config\Database;

/**
 * NOTE: This is not a valid file for actual tests.
 * This file came about as a small testbed I was using that
 * accidentally got committed. It will be removed prior to release
 * If you commit any changes to this file, it should be accompanied
 * by actual tests also.
 *
 * @package App\Controllers
 */
class Checks extends Controller
{
	use ResponseTrait;

	public function index()
	{
		session()->start();
	}
        
        public function forge()
        {
            echo '<h1>MySQL</h1>';
            
            log_message('debug', 'MYSQL TEST');
            
            $forge_mysql = \Config\Database::forge();
            
            $forge_mysql->getConnection()->query('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;');
            
            $forge_mysql->dropTable('users', true);
            
            $forge_mysql->getConnection()->query('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;');
            
            $forge_mysql->addField([
                'id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ]
            ]);
            $forge_mysql->addKey('id', true);
            $attributes = array('ENGINE' => 'InnoDB');
            $forge_mysql->createTable('users', true, $attributes);
            
            $data_insert = array(
                'id' => 1,
                'name' => 'User 1',
            );
            $forge_mysql->getConnection()->table('users')->insert($data_insert);

            $drop = $forge_mysql->dropTable('invoices', true);
            
            $forge_mysql->addField([
                'id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11,
                ],
                'users_id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11
                ],
                'other_id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11
                ]
            ]);
            $forge_mysql->addKey('id', true);

            $forge_mysql->addForeignKey('users_id','users','id','CASCADE','CASCADE');
            $forge_mysql->addForeignKey('other_id','users','id','CASCADE','CASCADE');

            $attributes = array('ENGINE' => 'InnoDB');
            $res = $forge_mysql->createTable('invoices', true,$attributes);

            if(!$res){
                var_dump($forge_mysql->getConnection()->mysqli);
            }else{
                echo '<br><br>OK';
                
                var_dump($forge_mysql->getConnection()->getForeignKeyData('invoices'));
            }
            
            $res = $forge_mysql->dropForeignKey('invoices','invoices_other_id_foreign');
            
            
            echo '<h1>PostgreSQL</h1>';
            
            $forge_pgsql = \Config\Database::forge('pgsql');

            $forge_pgsql->dropTable('users',true, true);
            
            $forge_pgsql->addField([
                'id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ]
            ]);
            $forge_pgsql->addKey('id', true);
            $forge_pgsql->createTable('users', true);
            
            
            $data_insert = array(
                'id' => 1,
                'name' => 'User 1',
            );
            $forge_pgsql->getConnection()->table('users')->insert($data_insert);
            
            $forge_pgsql->dropTable('invoices',true);
            $forge_pgsql->addField([
                'id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11,
                    'auto_increment' => true,
                ],
                'users_id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11
                ],
                'other_id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11
                ],
                'another_id' => [
                    'type' => 'INTEGER',
                    'constraint' => 11
                ]
            ]);
            $forge_pgsql->addKey('id', true);

            $forge_pgsql->addForeignKey('users_id','users','id','CASCADE','CASCADE'); 
            $forge_pgsql->addForeignKey('other_id','users','id');

            $res = $forge_pgsql->createTable('invoices', true);

            if(!$res){
                var_dump($forge_pgsql->getConnection()->mysqli);
            }else{
                echo '<br><br>OK';
                var_dump($forge_pgsql->getConnection()->getForeignKeyData('invoices'));
            }
            
            //$res = $forge_pgsql->dropForeignKey('invoices','invoices_other_id_foreign');
            
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
		$data = [
			"total_users" => 3,
			"users" => [
				[
					"id" => 1,
					"name" => "Nitya",
					"address" => [
						"country" => "India",
						"city" => "Kolkata",
						"zip" => 700102,
					]
				],
				[
					"id" => 2,
					"name" => "John",
					"address" => [
						"country" => "USA",
						"city" => "Newyork",
						"zip" => "NY1234",
					]
				],
				[
					"id" => 3,
					"name" => "Viktor",
					"address" => [
						"country" => "Australia",
						"city" => "Sydney",
						"zip" => 123456,
					]
				],
			]
		];

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

	public function db2()
	{
		$db = Database::connect();
		$db->initialize();

		$db->table('user')->insert([
				'name' => 'a',
				'email' => 'b@example.com',
				'country' => 'x'
			]);
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

	    dd($politician);

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

	public function redirect()
	{
		return redirect('/checks/model');
    }

	public function image()
	{
		$info = Services::image('imagick')
			->withFile("/Users/kilishan/Documents/BobHeader.jpg")
			->getFile()
			->getProperties(true);

		dd(ENVIRONMENT);

		$images = Services::image('imagick')
			->getVersion();
//			->withFile("/Users/kilishan/Documents/BobHeader.jpg")
//			->resize(500, 100, true)
//			->crop(200, 75, 20, 0, false)
//			->rotate(90)
//			->save('/Users/kilishan/temp.jpg');

//		$images = Services::image('imagick')
//			->withFile("/Users/kilishan/Documents/BobHeader.jpg")
//			->fit(500, 100, 'bottom-left')
//			->text('Bob is Back!', [
//				'fontPath'  => '/Users/kilishan/Downloads/Calibri.ttf',
//				'fontSize' => 40,
//				'padding' => 0,
//				'opacity'   => 0.5,
//				'vAlign'    => 'top',
//				'hAlign'    => 'right',
//				'withShadow' => true,
//			])
//			->save('/Users/kilishan/temp.jpg', 100);


		ddd($images);
	}

	public function time()
	{
		$time = new Time();

		echo($time);
		echo '<br/>';
		echo Time::now();
		echo '<br/>';
		echo Time::parse('First Monday of December');
		echo '<br/>';

		$time = new Time('Next Monday');
		die($time);
	}

	public function csp()
	{
//		$this->response->CSP->reportOnly(true);
		$this->response->CSP->setDefaultSrc(base_url());
		$this->response->CSP->addStyleSrc('unsafe-inline');
		$this->response->CSP->addStyleSrc('https://maxcdn.bootstrapcdn.com');

		echo <<<EOF
<!doctype html>
<html>
<head>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>
<style {csp-style-nonce}>
	body { background: #efefef; }
</style>

</body>
</html>
EOF;

	}

	public function upload()
	{
		if  ($this->request->getMethod() == 'post')
		{
			$this->validate([
				'avatar' => 'uploaded[avatar]|ext_in[avatar,png,jpg,jpeg,gif]'
			]);

			/**
			 * @var \CodeIgniter\HTTP\Files\UploadedFile
			 */
			$file = $this->request->getFile('avatar');

			echo "Name: {$file->getName()}<br/>";
			echo "Temp Name: {$file->getTempName()}<br/>";
			echo "Original Name: {$file->getClientName()}<br/>";
			echo "Random Name: {$file->getRandomName()}<br/>";
			echo "Extension: {$file->getExtension()}<br/>";
			echo "Client Extension: {$file->getClientExtension()}<br/>";
			echo "Guessed Extension: {$file->guessExtension()}<br/>";
			echo "MimeType: {$file->getMimeType()}<br/>";
			echo "IsValid: {$file->isValid()}<br/>";
			echo "Size (b): {$file->getSize()}<br/>";
			echo "Size (kb): {$file->getSize('kb')}<br/>";
			echo "Size (mb): {$file->getSize('mb')}<br/>";
			echo "Size (mb): {$file->getSize('mb')}<br/>";
			echo "Path: {$file->getPath()}<br/>";
			echo "RealPath: {$file->getRealPath()}<br/>";
			echo "Filename: {$file->getFilename()}<br/>";
			echo "Basename: {$file->getBasename()}<br/>";
			echo "Pathname: {$file->getPathname()}<br/>";
			echo "Permissions: {$file->getPerms()}<br/>";
			echo "Inode: {$file->getInode()}<br/>";
			echo "Owner: {$file->getOwner()}<br/>";
			echo "Group: {$file->getGroup()}<br/>";
			echo "ATime: {$file->getATime()}<br/>";
			echo "MTime: {$file->getMTime()}<br/>";
			echo "CTime: {$file->getCTime()}<br/>";

			dd($file);
		}

		echo <<<EOF
<!doctype html>
<html>
<body>

<form action="" method="post" enctype="multipart/form-data">

	<input type="file" name="avatar">

	<input type="submit" value="Upload">

</form>

</body>
</html>

EOF;
;
	}

	public function parser()
	{
		$this->parser = Services::parser();
	}

	public function error()
	{
		throw new \RuntimeException('Oops!', 403);
	}

}
