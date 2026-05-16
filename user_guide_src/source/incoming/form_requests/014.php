<?php

$input = $request->getValidatedInput();

$title = $input->get('title');
$slug  = $input->get('post.meta.slug', 'draft');

if ($input->has('note')) {
    // 'note' was validated, even if its value is null
}
