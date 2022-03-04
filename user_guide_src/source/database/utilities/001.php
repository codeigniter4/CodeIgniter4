<?php

$model                    = new class () extends \CodeIgniter\Model {
    protected $table      = 'foo';
    protected $primaryKey = 'id';
};
$db = \Closure::bind(static fn ($model) => $model->db, null, $model)($model);

$util = (new \CodeIgniter\Database\Database())->loadUtils($db);
echo $util->getXMLFromResult($model->get());
