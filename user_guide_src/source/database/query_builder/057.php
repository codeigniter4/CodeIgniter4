<?php

$groups = [1, 2, 3];
$builder->havingNotIn('group_id', $groups);
// Produces: OR group_id NOT IN (1, 2, 3)
