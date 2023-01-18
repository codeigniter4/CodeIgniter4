<?php

$where = 'CURRENT_TIMESTAMP() = DATE_ADD(column, INTERVAL 2 HOUR)';
$builder->where($where, null, false);
