<?php

// Fred Flintsone & Wilma
$validation->setRules([
    'contacts.friends.*.name' => 'required|max_length[60]',
]);
