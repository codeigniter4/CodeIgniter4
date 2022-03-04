<?php

$data = 'Here is some text!';
$name = 'mytext.txt';

return $response->download($name, $data);
