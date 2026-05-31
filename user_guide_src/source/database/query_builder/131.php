<?php

$names     = $builder->orderBy('name', 'ASC')->pluck('name');
$namesById = $builder->pluck('name', 'id');
