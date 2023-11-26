<?php

$data = $this->request->getPost();

$user = new \App\Entities\User();
$user->fill($data);
$userModel->save($user);
