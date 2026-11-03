<?php

$result = $builder->where('status', 'pending')->explain();
$plan   = $result->getResultArray();
