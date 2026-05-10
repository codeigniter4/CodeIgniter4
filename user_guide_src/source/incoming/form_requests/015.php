<?php

use App\Enums\PostStatus;

$input = $request->validatedInput();

$page        = $input->integer('page', 1);
$rating      = $input->float('rating', 0.0);
$active      = $input->boolean('active', false);
$publishedAt = $input->date('published_at', 'Y-m-d');
$status      = $input->enum('status', PostStatus::class, PostStatus::DRAFT);
