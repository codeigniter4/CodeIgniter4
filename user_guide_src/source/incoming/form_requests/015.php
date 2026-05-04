<?php

use App\Enums\PostStatus;

$page        = $request->integer('page', 1);
$active      = $request->boolean('active', false);
$publishedAt = $request->date('published_at', 'Y-m-d');
$status      = $request->enum('status', PostStatus::class);
