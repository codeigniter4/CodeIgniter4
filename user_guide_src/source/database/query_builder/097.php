<?php

$query = $builder->select('title')
    ->where('id', $id)
    ->limit(10, 20)
    ->get();
