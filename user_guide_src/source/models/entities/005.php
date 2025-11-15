<?php

$data = $this->request->getPost();

$user = new \App\Entities\User($data);
$userModel->save($user);
