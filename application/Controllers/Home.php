<?php

class Home extends \CodeIgniter\Controller
{

	public function index()
	{
        $db = \Config\Database::connect('default', true);
        $dados = $db->query("SELECT * FROM NEW_TABLE ORDER BY ID DESC")->getRow();
        
        //var_dump("INSERT INTO NEW_TABLE (NOME) VALUES (".$db->escapeString("sdsdsad").")"); die();
        
        //$db->query("INSERT INTO NEW_TABLE (NOME) VALUES (".$db->escapeString("asas'd").")");
        
        var_dump($db->escapeString(["sdsdsa'd", "sdasd"]));  
        if (is_null($dados)) { 
            die();
        }
        
		echo view('welcome_message');
	}

	//--------------------------------------------------------------------

}
