<?php

/*
 * The data to test:
 * [
 *     'user_ids' => [
 *         1,
 *         2,
 *         3,
 *     ]
 * ]
 */

// Rule
$validation->setRules([
    'user_ids.*' => 'required|max_length[19]',
]);
