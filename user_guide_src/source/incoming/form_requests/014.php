<?php

$title = $request->getValidated('title');
$slug  = $request->getValidated('post.meta.slug', 'draft');

if ($request->hasValidated('note')) {
    // 'note' was validated, even if its value is null
}
