<?php

// With closure
use CodeIgniter\Database\BaseBuilder;

$builder->whereIn('id', static function (BaseBuilder $builder) {
    $builder->select('job_id')->from('users_jobs')->where('user_id', 3);
});
// Produces: WHERE "id" IN (SELECT "job_id" FROM "users_jobs" WHERE "user_id" = 3)

// With builder directly
$subQuery = $db->table('users_jobs')->select('job_id')->where('user_id', 3);
$builder->whereIn('id', $subQuery);
