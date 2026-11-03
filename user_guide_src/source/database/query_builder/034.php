<?php

$names = ['Frank', 'Todd', 'James'];
$builder->whereNotIn('username', $names);
// Produces: WHERE username NOT IN ('Frank', 'Todd', 'James')
