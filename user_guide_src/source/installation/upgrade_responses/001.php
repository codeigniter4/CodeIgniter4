<?php

$this->response->setStatusCode(404);

// ...

return $this->response->setJSON(['foo' => 'bar']);
