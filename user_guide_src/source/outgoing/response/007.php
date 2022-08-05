<?php

$data = 'Here is some text!';
$name = 'mytext.txt';

return $this->response->download($name, $data);
