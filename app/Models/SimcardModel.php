<?php

namespace App\Models;

use CodeIgniter\Model;

class SimcardModel extends Model
{
    protected $table            = 'simcards';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['number', 'price', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'number' => 'required|exact_length[11]|is_unique[simcards.number,id,{id}]',
        'price'  => 'required|numeric',
        'status' => 'required|in_list[free,sold]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
