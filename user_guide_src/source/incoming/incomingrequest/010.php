<?php

/*
 * With a request body of:
 * {
 *     "foo": "bar",
 *     "fizz": {
 *         "buzz": "baz"
 *     }
 * }
 */

$data = $request->getVar('foo');
// $data = "bar"

$data = $request->getVar('fizz.buzz');
// $data = "baz"
