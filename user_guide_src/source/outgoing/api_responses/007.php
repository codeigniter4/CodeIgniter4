<?php

$user = $userModel->insert($data);

return $this->respondCreated($user);
