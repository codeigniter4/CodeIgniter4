<?php

namespace Tests\Support\Models;

use CodeIgniter\Model;

class StringifyPkeyModel extends Model
{
    protected $table      = 'stringifypkey';
    protected $returnType = 'object';
}
