<?php

namespace App\Models;

use CodeIgniter\Database\Query;
use CodeIgniter\Model;

class TestModel extends Model
{
    public function testDb() {
        /*$pQuery = $this->db->prepare(static function ($db) {
            $sql = 'INSERT INTO UTILISATEUR (CODE_UTILISATEUR, NOM, PRENOM, CODE_PROFIL) VALUES (?, ?, ?, ?)';

            return (new Query($db))->setQuery($sql);
        });

        $result = $pQuery->execute(
         'bh2',
            'HOUPERT',
            'Baptiste',
            1
        );
        var_dump($result);

        die('stop');*/

        /*$pQuery = $this->db->prepare(static function ($db) {
            return $db->table('UTILISATEUR')->insert([
                'CODE_UTILISATEUR'  => 'bh2',
                'NOM'               => 'HOUPERT',
                'PRENOM'            => 'Baptiste',
                'CODE_PROFIL'       => 1
            ]);
        });
        $a          = 'bh3';
        $b          = 'John Doe';
        $email      = 'j.doe@example.com';
        $country    = 1;

        $results = $pQuery->execute($a, $b, $email, $country);
        die('ok');*/

        /*$query = $this->db->table('UTILISATEUR')
            ->select('*')
            //->where("CODE_UTILISATEUR", 'admin')
            ->limit(3, 1)
            ->orderBy('CODE_UTILISATEUR', 'desc')
            ->get();*/

        /*$query = $this->db->query('select * from UTILISATEUR;');

        $query->dataSeek(4); // Skip the first 5 rows
        $row = $query->getUnbufferedRow();

        var_dump($row);*/

        /*$query = $this->db->getLastQuery();
        $microtime = $query->getDuration();
        die('durée : ' . $microtime);*/
        //die();

        /*$pQuery = $this->db->prepare(static function ($db) {
            $sql = 'select * from UTILISATEURS';

            return (new Query($db))->setQuery($sql);
        });*/


        /*$code = 'admin';
        $this->db->where('CODE_UTILISATEUR', $code);
        //$this->db->order_by('SEQUENCE', 'desc');
        $query = $this->db->get('UTILISATEUR');*/
        /*echo '<pre>';
        print_r($pQuery);
        echo '</pre>';*/

        /*$this->db->table('UTILISATEUR')->insert([
            'CODE_UTILISATEUR'  => 'bh2',
            'NOM'               => 'HOUPERT',
            'PRENOM'            => 'Baptiste',
            'CODE_PROFIL'       => 1
        ]);
        echo '$this->db->insertID() = ' . $this->db->insertID() . '<br />';
        echo '$this->db->affectedRows() = ' . $this->db->affectedRows() . '<br />';
        echo '$this->db->getLastQuery() = ' . $this->db->getLastQuery() . '<br />';*/
        //echo '$this->db->countAll() sur CMDE_CLIENT = ' . $this->db->table('CMDE_CLIENT')->countAll() . '<br />';
        //echo 'countAllResults UTILISATEUR avec CODE_PROFIL 1 ' . $this->db->table('UTILISATEUR')->like('CODE_PROFIL', 8)->countAllResults() . '<br />';
        echo $this->db->getVersion() . '<br />';

        /*
        $nomColonne = 'toto';
        if($this->db->fieldExists($nomColonne, 'UTILISATEUR'))
            echo 'La colonne ' . $nomColonne . ' existe<br />';
        else
            echo 'La colonne ' . $nomColonne . ' n\'existe pas<br />';
        $nomColonne = 'NOM';
        if($this->db->fieldExists($nomColonne, 'UTILISATEUR'))
            echo 'La colonne ' . $nomColonne . ' existe<br />';
        else
            echo 'La colonne ' . $nomColonne . ' n\'existe pas<br />';
        */
        /*
        $query = $this->db->query('SELECT * FROM CMDE_CLIENT');
        foreach ($query->getFieldNames() as $field)
            echo $field . '<br />';
        */
        /*
        $fields = $this->db->getFieldNames('UTILISATEUR');
        foreach ($fields as $field) {
            echo $field . '<br />';
        }
        */

        /*
        $nomTable = 'UTILISATEUR';
        if($this->db->tableExists($nomTable))
            die('La table ' . $nomTable . ' existe');
        else
            die('La table ' . $nomTable . ' n\'existe pas');
        */

        /*$tables = $this->db->listTables();
        foreach ($tables as $table) {
            echo $table . '<br />';
        }*/

        /*$fields = $this->db->getFieldData('UTILISATEUR');
        foreach ($fields as $field) {
            echo 'Nom : ' . $field->name . '<br />';
            echo 'Type : ' . $field->type . '<br />';
            echo 'Valeur défaut : ' . $field->default . '<br />';
            echo 'Longueur max : ' . $field->max_length . '<br />';
            echo 'Clé primaire ? : ' . $field->primary_key . '<br />';
            echo 'Null ? : ' . $field->nullable . '<br />' . '<br />';
        }*/

        /*$nomTable = 'ED_TRANSFERTS_PARAMETRES';
        $keys = $this->db->getIndexData($nomTable);
        echo 'Indexes de la table ' . $nomTable . ' :<br />';
        foreach ($keys as $key) {
            echo 'Nom : ' . $key->name . '<br />';
            echo 'Type : ' . $key->type . '<br />';
            echo 'Colonne(s) : ' . print_r($key->fields, true) . '<br />' . '<br />'; // array of field names
        }*/

        $keys = $this->db->getForeignKeyData('ED_TRANSFERTS_PARAMETRES');
        print_r($keys);
        /*foreach ($keys as $key => $object) {
            echo $key === $object->constraint_name;
            echo $object->constraint_name;
            echo $object->table_name;
            echo $object->column_name[0]; // array
            echo $object->foreign_table_name;
            echo $object->foreign_column_name[0]; // array
            echo $object->on_delete;
            echo $object->on_update;
            echo $object->match;
        }*/

        die();
    }


}
