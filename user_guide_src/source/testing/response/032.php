<?php

/*
 * Response body is this:
 * [
 *     'config' => ['key-a', 'key-b'],
 * ]
 */

// Is true
$result->assertJSONFragment(['config' => ['key-a']]);
