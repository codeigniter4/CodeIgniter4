<?php

$results = $this->withUri('http://example.com/forums/categories')
    ->controller(\App\Controllers\ForumController::class)
    ->execute('showCategories');
