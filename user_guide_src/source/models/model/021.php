<?php

use CodeIgniter\Model;

class JobModel extends Model
{
    protected $table = 'jobs';
    protected $returnType = '\App\Entities\Job';
    protected $allowedFields = [
        'name', 'description'
    ];
}
