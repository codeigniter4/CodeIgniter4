<?php

$this->output->set_status_header(404);

// ...

$this->output
    ->set_content_type('application/json')
    ->set_output(json_encode(array('foo' => 'bar')));
