<?php

$names = ['Frank', 'Todd', 'James'];
$builder->orWhereNotIn('username', $names);
// Produces: OR username NOT IN ('Frank', 'Todd', 'James')
