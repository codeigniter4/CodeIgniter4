<?php

// Fred Flintsone & Wilma
$validation->setRules([
    'contacts.*.name' => 'required|max_length[60]',
]);
