<?php

class MyModel extends \CodeIgniter\Model
{
    protected $table      = 'foo';
    protected $primaryKey = 'id';
}

$model = new MyModel();

$util = \CodeIgniter\Database\Config::utils();

echo $util->getXMLFromResult($model->get());
