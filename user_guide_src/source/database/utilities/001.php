<?php

$model                    = new class () extends \CodeIgniter\Model {
    protected $table      = 'foo';
    protected $primaryKey = 'id';
};

$util = \CodeIgniter\Database\Config::utils();

echo $util->getXMLFromResult($model->get());
