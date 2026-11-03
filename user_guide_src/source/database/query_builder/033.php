<?php

// With closure
use CodeIgniter\Database\BaseBuilder;

$builder->orWhereIn('id', static function (BaseBuilder $builder) {
    $builder->select('job_id')->from('users_jobs')->where('user_id', 3);
});
// Produces: OR "id" IN (SELECT "job_id" FROM "users_jobs" WHERE "user_id" = 3)

// With builder directly
$subQuery = $db->table('users_jobs')->select('job_id')->where('user_id', 3);
$builder->orWhereIn('id', $subQuery);
