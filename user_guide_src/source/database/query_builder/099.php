<?php

use CodeIgniter\Database\RawSql;

$sql = 'REGEXP_SUBSTR(ral_anno,"[0-9]{1,2}([,.][0-9]{1,3})([,.][0-9]{1,3})") AS ral';
$builder->select(new RawSql($sql));
$query = $builder->get();
