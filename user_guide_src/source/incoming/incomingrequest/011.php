<?php

// With the same request as above
$data = $request->getJsonVar('fizz');
// $data->buzz = "baz"

$data = $request->getJsonVar('fizz', true);
// $data = ["buzz" => "baz"]
