<?php

/*
 * The data to test:
 * [
 *     'contacts' => [
 *         'name' => 'Joe Smith',
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
