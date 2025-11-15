<?php

// Generates a message like: User 123 logged into the system from 127.0.0.1
$info = [
    'id'         => $user->id,
    'ip_address' => $this->request->getIPAddress(),
];

log_message('info', 'User {id} logged into the system from {ip_address}', $info);
