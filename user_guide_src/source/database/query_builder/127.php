<?php

$builder->select('category')
    ->selectCount('id', 'total')
    ->groupBy('category')
    ->havingBetween('COUNT(id)', [10, 20], false);
