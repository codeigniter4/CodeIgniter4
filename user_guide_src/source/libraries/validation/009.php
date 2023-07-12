<?php

/*
 * The data to test:
 * [
 *     'contacts' => [
 *        'name' => 'Joe Smith',
 *         'friends' => [
 *             [
 *                 'name' => 'Fred Flinstone',
 *             ],
 *             [
 *                 'name' => 'Wilma',
 *             ],
 *         ]
 *     ]
 * ]
 */

// Joe Smith
$validation->setRules([
    'contacts.name' => 'required|max_length[60]',
]);

// Fred Flintsone & Wilma
$validation->setRules([
    'contacts.friends.name' => 'required|max_length[60]',
]);
