<?php

$status = service('request')->getPost('status');

$users = $this->db->table('users')
    ->when($status, static function ($query, $status) {
        $query->where('status', $status);
    })
    ->get();
