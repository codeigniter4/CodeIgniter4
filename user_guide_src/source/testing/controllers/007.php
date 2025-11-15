<?php

$response = new \CodeIgniter\HTTP\Response(new \Config\App());

$results = $this->withResponse($response)
    ->controller(\App\Controllers\ForumController::class)
    ->execute('showCategories');
