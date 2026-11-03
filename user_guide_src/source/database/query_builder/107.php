<?php

$status = service('request')->getPost('status');

$users = $this->db->table('users')
    ->whenNot($status, static function ($query, $status) {
        $query->where('active', 0);
    })
    ->get();
