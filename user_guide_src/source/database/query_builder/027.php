<?php

$name  = $builder->db->escape('Joe');
$where = "name={$name} AND status='boss' OR status='active'";
$builder->where($where);
