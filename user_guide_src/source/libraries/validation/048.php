<?php

use App\Enums\PostStatus;

$validation = service('validation');
$validation->setRules([
    'title'        => 'required|string',
    'page'         => 'permit_empty|integer',
    'active'       => 'permit_empty|in_list[0,1,true,false,yes,no,on,off]',
    'tags'         => 'permit_empty|is_array',
    'published_at' => 'permit_empty|valid_date[Y-m-d]',
    'status'       => 'permit_empty|in_list[draft,published]',
]);

$data = [
    'title'        => 'Hello World',
    'page'         => '2',
    'active'       => 'true',
    'tags'         => ['php', 'codeigniter'],
    'published_at' => '2026-05-04',
    'status'       => 'published',
];

if (! $validation->run($data)) {
    // The validation failed.
    return;
}

$input = $validation->getValidatedInput();

$title       = $input->string('title');
$page        = $input->integer('page', 1);
$active      = $input->boolean('active', false);
$tags        = $input->array('tags', []);
$publishedAt = $input->date('published_at', 'Y-m-d');
$status      = $input->enum('status', PostStatus::class, PostStatus::DRAFT);
