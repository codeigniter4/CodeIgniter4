<?php

/*
 * The data to test:
 * [
 *     'contacts' => [
 *        'name' => 'Joe Smith',
 *         'friends' => [
 *             'name' => 'Fred Flinstone',
 *         ]
 *     ]
 * ]
 */

// Joe Smith
$validation->setRules([
    'contacts.name' => 'required|max_length[60]',
]);

// Fred Flintsone
$validation->setRules([
    'contacts.friends.name' => 'required|max_length[60]',
]);
