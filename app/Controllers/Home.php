<?php

namespace App\Controllers;

use App\Models\MajOc2iApiModel;
use App\Models\TestModel;

class Home extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        /*$query = $db->table('UTILISATEUR')->get();

        foreach ($query->getResult() as $row) {
            echo '<pre>';
            print_r($row);
            echo '</pre>';
        }*/



        /*$data = [
            'CODE_UTILISATEUR'  => 'bh',
            'NOM'               => 'HOUPERT',
            'PRENOM'            => 'Baptiste',
            'CODE_PROFIL'       => 1
        ];

        $db->table('UTILISATEUR')->insert($data);*/

        /*$query   = $db->query('select * from UTILISATEUR');
        $results = $query->getResultArray();

        foreach ($results as $row) {
            echo $row['CODE_UTILISATEUR'] . '<br />';
            echo $row['NOM'] . '<br />';
            echo $row['PRENOM'] . '<br />' . '<br />';
        }*/

        /*if (! $db->simpleQuery('SELECT * FROM UTILISATEUR')) {
            die($db->error()); // Has keys 'code' and 'message'
        }

        $sql = 'SELECT * FROM UTILISATEUR WHERE CODE_PROFIL = :profil:';
        $query = $db->query($sql, [ 'profil' => 1]);
        $results = $query->getResultArray();

        foreach ($results as $row) {
            echo $row['CODE_UTILISATEUR'] . '<br />';
            echo $row['NOM'] . '<br />';
            echo $row['PRENOM'] . '<br />' . '<br />';
        }

        die();*/

        $testModel = new TestModel();
        $testModel->testDb();
        /*//$db = db_connect();
        $db = \Config\Database::connect();
        //$code = 'admin';
        //$db->where('CODE_UTILISATEUR', $code);
        //$this->db->order_by('SEQUENCE', 'desc');
        $query = $db->get('UTILISATEUR');
        echo '<pre>';
        print_r($query->row());
        echo '</pre>';
        die();
        if($db->query($sql)) {
            echo 'Success!';
        } else {
            echo 'Query failed!';
        }
        die('');*/
        return view('welcome_message');
    }
}
