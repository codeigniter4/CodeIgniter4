<?php

$names = ['Frank', 'Todd', 'James'];
$builder->orWhereIn('username', $names);
// Produces: OR username IN ('Frank', 'Todd', 'James')
