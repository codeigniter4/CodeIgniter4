<?php

// With closure
use CodeIgniter\Database\BaseBuilder;

$builder->orHavingNotIn('id', static function (BaseBuilder $builder) {
    $builder->select('user_id')->from('users_jobs')->where('group_id', 3);
});
// Produces: OR "id" NOT IN (SELECT "user_id" FROM "users_jobs" WHERE "group_id" = 3)

// With builder directly
$subQuery = $db->table('users_jobs')->select('user_id')->where('group_id', 3);
$builder->orHavingNotIn('id', $subQuery);
