<?php

$groups = [1, 2, 3];
$builder->orHavingIn('group_id', $groups);
// Produces: OR group_id IN (1, 2, 3)
