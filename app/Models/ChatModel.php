<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatModel extends Model
{
    protected $table            = 'chats';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; // Or 'object'
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['message'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime'; // 'datetime', 'date', or 'int'
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // No updated_at field
    // protected $deletedField  = 'deleted_at'; // No soft deletes

    // Validation
    // protected $validationRules      = [];
    // protected $validationMessages   = [];
    // protected $skipValidation       = false;
    // protected $cleanValidationRules = true;

    // Callbacks
    // protected $allowCallbacks = true;
    // protected $beforeInsert   = [];
    // protected $afterInsert    = [];
    // protected $beforeUpdate   = [];
    // protected $afterUpdate    = [];
    // protected $beforeFind     = [];
    // protected $afterFind      = [];
    // protected $beforeDelete   = [];
    // protected $afterDelete    = [];
}
