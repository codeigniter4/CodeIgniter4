<?php

$groups = [1, 2, 3];
$builder->havingIn('group_id', $groups);
// Produces: HAVING group_id IN (1, 2, 3)
