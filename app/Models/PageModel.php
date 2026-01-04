<?php

namespace App\Models;

use CodeIgniter\Model;

class PageModel extends Model
{
    protected $table            = 'pages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'project_id', 'page_number', 'layout_type', 'title',
        'content', 'ai_suggestions', 'is_approved', 'rendered_image'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $casts = [
        'content' => 'json',
        'ai_suggestions' => 'json',
        'is_approved' => 'boolean',
    ];
}
