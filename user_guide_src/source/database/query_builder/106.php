<?php

$onlyInactive = service('request')->getPost('return_inactive');

$users = $this->db->table('users')
    ->when($onlyInactive, function($query, $onlyInactive) {
        $query->where('status', 'inactive');
    }, function($query) {
        $query->where('status', 'active');
    })
    ->get();
