<?php

$results = $this->withURI('http://example.com/forums/categories')
    ->controller(\App\Controllers\ForumController::class)
    ->execute('showCategories');
