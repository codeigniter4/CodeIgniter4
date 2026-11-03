<?php

$data = [
    'success' => true,
    'id'      => 123,
];

return $this->response->setJSON($data);

// or
return $this->response->setXML($data);
