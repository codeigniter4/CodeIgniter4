<?php

$builder->orderBy('title', 'RANDOM');
// Produces: ORDER BY RAND()

$builder->orderBy(42, 'RANDOM');
// Produces: ORDER BY RAND(42)
