<?php namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class Checks extends Controller
{
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


}
